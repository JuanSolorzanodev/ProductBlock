<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory,SoftDeletes;

    // Definir la tabla asociada al modelo
    protected $table = 'product_images';

    // Definir los campos que se pueden asignar de forma masiva
    protected $fillable = [
        'product_id',
        'image_path',
        'name',
        'size',
        'top',
    ];

    // Relación con el modelo Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
