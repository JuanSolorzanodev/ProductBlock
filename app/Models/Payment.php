<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['order_id','first_name','last_name','locality','address','postal_code','phone','country','province','canton', 'payment_date', 'amount', 'payment_method', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}