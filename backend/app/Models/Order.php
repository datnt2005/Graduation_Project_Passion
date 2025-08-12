<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'address_id',
        'discount_id',
        'note',
        'status',
        'failure_reason',
        'is_buy_now',
        'total_price',
        'discount_price',
        'shipping_fee',
        'shipping_discount',
        'final_price',
        'shipping_method',
    ];

    protected $casts = [
        'status' => 'string',
        'total_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'shipping_discount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'failure_reason' => 'string',
        'discount_id' => 'array', // Cast discount_id thành array để lưu JSON
    ];

    public const STATUSES = [
        'pending',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
        'failed',
        'failed_delivery',
        'rejected_by_customer',
    ];

    public function getStatusText(): string
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã gửi hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'failed' => 'Giao thất bại',
            'failed_delivery' => 'Giao không thành công',
            'rejected_by_customer' => 'Khách từ chối nhận',
        ];
        return $statusMap[$this->status] ?? $this->status;
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Sửa relationship discount để hỗ trợ nhiều discount
    public function discounts()
    {
        if (!$this->discount_id || !is_array($this->discount_id)) {
            return collect();
        }
        
        return Discount::whereIn('id', $this->discount_id)->get();
    }

    // Giữ lại relationship cũ để tương thích ngược
    public function discount()
    {
        if (!$this->discount_id || !is_array($this->discount_id)) {
            return $this->belongsTo(Discount::class)->withDefault();
        }
        
        $firstDiscountId = is_array($this->discount_id) ? ($this->discount_id['product'] ?? $this->discount_id['shipping'] ?? null) : $this->discount_id;
        return $this->belongsTo(Discount::class, 'discount_id')->withDefault();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'order_id', 'id');
    }

    public function payout()
    {
        return $this->hasOne(Payout::class, 'order_id', 'id');
    }

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }

    // Phương thức mới để lấy shipping discount
    public function getShippingDiscount()
    {
        if (!$this->discount_id || !is_array($this->discount_id)) {
            return null;
        }
        
        $shippingDiscountId = $this->discount_id['shipping'] ?? null;
        if (!$shippingDiscountId) {
            return null;
        }
        
        return Discount::find($shippingDiscountId);
    }

    // Phương thức mới để lấy product discount
    public function getProductDiscount()
    {
        if (!$this->discount_id || !is_array($this->discount_id)) {
            return null;
        }
        
        $productDiscountId = $this->discount_id['product'] ?? null;
        if (!$productDiscountId) {
            return null;
        }
        
        return Discount::find($productDiscountId);
    }

    // Phương thức mới để áp dụng nhiều discount
    public function applyMultipleDiscounts($discountIds)
    {
        $discounts = Discount::whereIn('id', $discountIds)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->get();

        if ($discounts->isEmpty()) {
            throw new \Exception('Không tìm thấy mã giảm giá hợp lệ');
        }

        $productDiscount = null;
        $shippingDiscount = null;
        $totalDiscountAmount = 0;

        foreach ($discounts as $discount) {
            // Kiểm tra giới hạn sử dụng
            if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                throw new \Exception("Mã giảm giá {$discount->code} đã hết lượt sử dụng");
            }

            if ($discount->discount_type === 'shipping_fee') {
                $shippingDiscount = $discount;
            } else {
                $productDiscount = $discount;
            }
        }

        // Tính toán discount cho sản phẩm
        if ($productDiscount) {
            if ($productDiscount->min_order_value && $this->total_price < $productDiscount->min_order_value) {
                throw new \Exception('Giá trị đơn hàng chưa đạt mức tối thiểu để sử dụng mã giảm giá sản phẩm');
            }

            if ($productDiscount->discount_type === 'percentage') {
                $totalDiscountAmount += $this->total_price * ($productDiscount->discount_value / 100);
            } else {
                $totalDiscountAmount += $productDiscount->discount_value;
            }
        }

        // Lưu discount IDs dưới dạng JSON
        $discountData = [];
        if ($productDiscount) {
            $discountData['product'] = $productDiscount->id;
        }
        if ($shippingDiscount) {
            $discountData['shipping'] = $shippingDiscount->id;
        }

        // Cập nhật thông tin đơn hàng
        $this->discount_id = $discountData;
        $this->discount_price = $totalDiscountAmount;
        $this->final_price = $this->total_price - $totalDiscountAmount;
        $this->save();

        // Tăng số lần sử dụng của các mã giảm giá
        foreach ($discounts as $discount) {
            $discount->increment('used_count');
        }

        return [
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công',
            'discount_amount' => $totalDiscountAmount,
            'final_price' => $this->final_price,
            'product_discount' => $productDiscount,
            'shipping_discount' => $shippingDiscount
        ];
    }

    // Phương thức kiểm tra và áp dụng mã giảm giá (giữ lại để tương thích)
    public function applyDiscount($discountCode)
    {
        // Tìm mã giảm giá
        $discount = Discount::where('code', $discountCode)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$discount) {
            throw new \Exception('Mã giảm giá không hợp lệ hoặc đã hết hạn');
        }

        // Kiểm tra giới hạn sử dụng
        if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
            throw new \Exception('Mã giảm giá đã hết lượt sử dụng');
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($discount->min_order_value && $this->total_price < $discount->min_order_value) {
            throw new \Exception('Giá trị đơn hàng chưa đạt mức tối thiểu để sử dụng mã giảm giá');
        }

        // Tính toán giá trị giảm giá
        $discountAmount = 0;
        if ($discount->discount_type === 'percentage') {
            $discountAmount = $this->total_price * ($discount->discount_value / 100);
        } else {
            $discountAmount = $discount->discount_value;
        }

        // Cập nhật thông tin đơn hàng
        $this->discount_id = $discount->id;
        $this->discount_price = $discountAmount;
        $this->final_price = $this->total_price - $discountAmount;
        $this->save();

        // Tăng số lần sử dụng của mã giảm giá
        $discount->increment('used_count');

        return [
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công',
            'discount_amount' => $discountAmount,
            'final_price' => $this->final_price
        ];
    }

    // Phương thức xóa mã giảm giá
    public function removeDiscount()
    {
        if ($this->discount_id) {
            $this->discount_id = null;
            $this->discount_price = 0;
            $this->final_price = $this->total_price;
            $this->save();

            return [
                'success' => true,
                'message' => 'Đã xóa mã giảm giá',
                'final_price' => $this->final_price
            ];
        }

        throw new \Exception('Đơn hàng chưa áp dụng mã giảm giá');
    }
    

    /**
     * Kiểm tra trạng thái hợp lệ
     */
    public function isValidStatus($status)
    {
        return in_array($status, [
            'pending',
            'processing',
            'shipping',
            'shipped',
            'delivered',
            'cancelled',
            'failed',
            'failed_delivery',
            'rejected_by_customer'
        ]);
    }

    public function canTransitionTo($newStatus)
    {
        $transitions = [
            'pending' => ['processing', 'shipping', 'cancelled'],
            'confirmed' => ['processing', 'cancelled', 'shipping'],
            'processing' => ['shipped', 'cancelled', 'shipping'],
            'shipping' => ['shipped', 'failed', 'failed_delivery', 'rejected_by_customer', 'delivered'],
            'shipped' => ['delivered', 'failed', 'failed_delivery', 'rejected_by_customer'],
            'failed' => ['rejected_by_customer', 'failed_delivery', 'cancelled'],
            'failed_delivery' => ['rejected_by_customer', 'cancelled'],
            'rejected_by_customer' => ['cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}
