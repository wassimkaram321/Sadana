<?php

namespace App\Model;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $casts = [
        'product_id'  => 'integer',
        'customer_id' => 'integer',
        'rating'      => 'integer',
        'status'      => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    protected $fillable = [
        'rating',
        'comment',
        'product_id',
        'attachment',
        'customer_id',
        'status'
    ];


    public function user()
    {
        return $this->hasOne('App\User', 'id', 'customer_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function delivery()
    {
        return $this->belongsTo(DeliveryMan::class,'product_id');
    }
}
