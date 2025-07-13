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
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class)->withDefault();
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

    // Phương thức kiểm tra và áp dụng mã giảm giá
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

        // Lưu lịch sử sử dụng mã giảm giá
        DiscountUser::create([
            'discount_id' => $discount->id,
            'user_id' => $this->user_id,
            'is_used' => true,
            'redeemed_at' => now()
        ]);

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

    public function shipping()
    {
        return $this->hasOne(Shipping::class);
    }
}
