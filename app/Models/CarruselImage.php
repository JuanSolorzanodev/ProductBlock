<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use CloudinaryLabs\CloudinaryLaravel\MediaAlly;


class CarruselImage extends Model
{
    use HasFactory, SoftDeletes,MediaAlly;
    protected $fillable = [
        'image_path',
        'name',
        'size',
        'top',
    ];
}
