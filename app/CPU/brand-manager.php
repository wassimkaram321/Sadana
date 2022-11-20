<?php

namespace App\CPU;

use App\Model\Brand;
use App\Model\Product;
use App\Model\ProductPoint;

class BrandManager
{
    public static function get_brands()
    {
        return Brand::withCount('brandProducts')->latest()->get();
    }

    public static function get_products($brand_id)
    {
        $products = Product::where(['brand_id' => $brand_id])->where('status','=',1)->get()
         ->makeHidden(
                [
                    'added_by', 'user_id', 'category_ids',
                    'flash_deal', 'video_provider', 'video_url', 'colors',
                    'variant_product', 'attributes', 'choice_options', 'variation',
                    'published', 'tax', 'tax_type','attachment',
                    'meta_title', 'meta_description', 'meta_image', 'request_status','denied_note',
                    'temp_shipping_cost', 'is_shipping_cost_updated', 'store_id', 'num_id',
                    'created_at', 'updated_at','min_qty','multiply_qty','shipping_cost',
                ]
            )
        ->toArray();



        $points = ProductPoint::where('type','product')->get();
        $array = [];
        foreach($products as $p){

               foreach($points as $point){

                   $idx = json_decode($point->type_id);
                   foreach($idx as $d){

                       if($p['id'] == $d){
                           $p['points'] = $point->points;
                        //   $p->save();
                          $array[] = $p;


                       }
                       else{
                           $p['points'] = '0';
                           $array[] = $p;
                       }
                   }
               }
            }
        return $array;
    }
}
