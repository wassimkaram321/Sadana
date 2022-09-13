<?php

namespace App\Http\Controllers\api\v1;
use App\CPU\Helpers;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use App\Model\Product;
use App\Model\Bag;
use App\Model\BagProduct;
use App\Model\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class BagController extends Controller
{
    public function get_bags()
    {
        try {
            $bags = Bag::get()->makeHidden(
                [
                    'updated_at', 'created_at', 'deleted_at'
                ]
            );
        } catch (\Exception $e) {
        }

        return response()->json($bags, 200);
    }

    public function get_bag_products(Request $request)
    {
        try {
            $bag_products = BagProduct::join("products", "products.id", "=", "products_bag.product_id")
            ->where("products_bag.bag_id", $request->bag_id)
            ->get([
                'products.name', 'products.thumbnail',
                'products_bag.product_count',
                'products_bag.product_price', 'products_bag.product_total_price',
            ]);

        } catch (\Exception $e) {
        }

        return response()->json($bag_products, 200);
    }
}
