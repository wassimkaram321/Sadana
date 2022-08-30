<?php

namespace App\CPU;

use App\Model\Brand;
use App\Model\Product;

class BrandManager
{
    public static function get_brands()
    {
        return Brand::withCount('brandProducts')->latest()->get();
    }

    public static function get_products($brand_id)
    {
        return Helpers::product_data_formatting(
            Product::where(['brand_id' => $brand_id])->get()->makeHidden(
                [
                    'added_by', 'user_id', 'category_ids', 'slug',
                    'flash_deal', 'video_provider', 'video_url', 'colors',
                    'variant_product', 'attributes', 'choice_options', 'variation',
                    'published', 'purchase_price', 'tax', 'tax_type','attachment',
                    'meta_title', 'meta_description', 'meta_image', 'request_status','denied_note',
                    'temp_shipping_cost', 'is_shipping_cost_updated', 'store_id', 'num_id',
                    'created_at', 'updated_at','min_qty'
                ]
            )
        , true);
    }
}
