<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [ 'seller_id', 'name', 'slug', 'description', 'status', 'is_admin_added'];
    public function productVariants(){
      return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function productPic()
    {
        return $this->hasMany(ProductPic::class, 'product_id', 'id');
    }


    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tags', 'product_id', 'tag_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'id');
    }

    public function scopeFilter($query, array $filters)
    {
        if ($filters['search'] ?? false) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }
        if ($filters['category'] ?? false) {
            $query->whereHas('categories', function ($query) {
                $query->where('slug', request('category'));
            });
        }
        if ($filters['tag'] ?? false) {
            $query->whereHas('tags', function ($query) {
                $query->where('slug', request('tag'));
            });
        }
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function pics()
    {
        return $this->hasMany(ProductPic::class, 'product_id'); // Giả sử bảng `product_pics` có cột `product_id`
    }
}
