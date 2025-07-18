<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductApproval extends Model
{
    protected $fillable = [
        'product_id',
        'admin_id',
        'status',
        'reason',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')
                    ->withDefault([
                        'name' => 'Sản phẩm không tồn tại'
                    ]);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'id')
                    ->withDefault([
                        'name' => 'Admin không tồn tại'
                    ]);
    }
}
