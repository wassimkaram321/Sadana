<?php

namespace App\CPU;

use App\Model\Brand;
use App\Model\Product;
use App\Model\ProductPoint;
use App\Model\Bonus;

class BrandManager
{
    public static function get_brands()
    {
        return Brand::withCount('brandProducts')->latest()->get();
    }

    public static function get_products($brand_id)
    {
        $products = Product::active()->with(['rating'])->where(['brand_id' => $brand_id])->where('status', '=', 1)->get()
            ->makeHidden(
                [
                    'added_by', 'user_id', 'category_ids',
                    'flash_deal', 'video_provider', 'video_url', 'colors',
                    'variant_product', 'attributes', 'choice_options', 'variation',
                    'published', 'tax', 'tax_type', 'attachment',
                    'meta_title', 'meta_description', 'meta_image', 'request_status', 'denied_note',
                    'temp_shipping_cost', 'is_shipping_cost_updated', 'store_id', 'num_id',
                    'created_at', 'updated_at', 'min_qty', 'multiply_qty', 'shipping_cost',
                ]
            );

        //$points = ProductPoint::where('type', 'product')->get();
        $pointNew = "0";
        foreach ($products as $p) {

            // foreach ($points as $point) {
            //     $idx = json_decode($point->type_id);
            //     foreach ($idx as $d) {
            //         if ($p['id'] == $d) {
            //             $pointNew = $point->points;
            //         } else {
            //             $pointNew = "0";
            //         }
            //     }
            // }
            $p['points'] = $pointNew;
        }
        return $products;
    }
}
