<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSpecification extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_specifications';

    protected $fillable = [
        'product_id',
        'packaging_type',
        'material',
        'usage_location',
        'color',
        'load_capacity',
        'country_of_origin',
        'warranty',
        'number_of_pieces',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
