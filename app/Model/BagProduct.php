<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class BagProduct extends Model
{
    protected $table = 'products_bag';
    protected $casts = [

        'total_price_offer '    => 'integer',
        'bag_status '     => 'integer',
    ];
    protected $fillable = [
        'bag_name',
        'bag_description',
        'total_price_offer',
        'bag_image',
        'bag_status',
        'end_date',
    ];
    protected $hidden=[
        'created_at',
        'updated_at'
    ];
}
