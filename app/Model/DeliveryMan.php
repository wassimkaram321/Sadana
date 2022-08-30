<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeliveryMan extends Model
{
    protected $hidden = ['password','auth_token'];

    protected $casts = [
        'is_active'=>'integer'
    ];


    //table delivery reviews
    public function dReviews()
    {
        return $this->hasMany(DeliveryReview::class,'delivery_id');
    }



    //table reviews
    public function reviews()
    {
        return $this->hasMany(Review::class,'product_id');
    }
}
