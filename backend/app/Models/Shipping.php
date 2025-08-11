<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    protected $table = 'shipping';
    protected $fillable = [
        'order_id',
        'shipping_method_id',
        'estimated_delivery',
        'shipping_fee',
        'shipping_discount',
        'tracking_code',
        'status',
    ];

    protected $casts = [
        'estimated_delivery' => 'datetime',
        'shipping_fee' => 'float',
        'shipping_discount' => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    // Phương thức tính phí vận chuyển cuối cùng sau khi áp dụng discount
    public function getFinalShippingFee()
    {
        $originalFee = $this->shipping_fee ?? 0;
        $discount = $this->shipping_discount ?? 0;
        return max(0, $originalFee - $discount);
    }

    // Phương thức áp dụng shipping discount
    public function applyShippingDiscount($discountAmount)
    {
        $this->shipping_discount = $discountAmount;
        $this->save();
        return $this->getFinalShippingFee();
    }

    // Phương thức lấy thông tin discount từ order
    public function getShippingDiscountInfo()
    {
        if (!$this->order || !$this->order->discount_id) {
            return null;
        }

        $discountIds = $this->order->discount_id;
        if (!is_array($discountIds) || !isset($discountIds['shipping'])) {
            return null;
        }

        return Discount::find($discountIds['shipping']);
    }
}
