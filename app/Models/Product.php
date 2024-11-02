<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
   
    use HasFactory,SoftDeletes;

    protected $fillable = ['name', 'description', 'price', 'stock', 'SKU', 'iva', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_suppliers');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function specifications()
    {
        return $this->hasOne(ProductSpecification::class);
    }
}
