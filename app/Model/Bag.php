<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Bag extends Model
{
    protected $table = 'bag';
    protected $casts = [

        'product_id '    => 'integer',
        'bag_id '     => 'integer',
        'product_price' => 'integer',
        'product_count' => 'integer',
        'product_total_price' => 'integer',
        'is_gift' => 'integer',
    ];
    protected $fillable = [
        'product_id',
        'product_price',
        'bag_id',
        'product_count',
        'product_total_price',
        'is_gift',
    ];

    protected $hidden=[
        'created_at',
        'updated_at'
    ];

    public function bag_order_details()
    {
        return $this->hasMany(BagsOrdersDetails::class, 'bag_id');
    }

    public function bag_order_delivered()
    {
        return $this->hasMany(BagsOrdersDetails::class, 'bag_id')
                        ->where('delivery_status','pending');
    }


}
