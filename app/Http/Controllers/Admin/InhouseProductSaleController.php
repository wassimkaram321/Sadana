<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Category;
use App\Model\Product;
use App\Model\Bag;
use Illuminate\Http\Request;

class InhouseProductSaleController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where(['parent_id' => 0])->get();
        $query_param = ['category_id' => $request['category_id']];

        $products = Product::where(['added_by' => 'admin'])
            ->when($request->has('category_id') && $request['category_id'] != 'all', function ($query) use ($request) {
                $query->whereJsonContains('category_ids', [[['id' => (string)$request['category_id']]]]);
            })->with(['order_details'])->paginate(Helpers::pagination_limit(), ['*'], 'product-page')
            ->appends($query_param);
        $category_id = $request['category_id'];

        $bags = Bag::with(['bag_order_details'])
        ->paginate(Helpers::pagination_limit(), ['*'], 'bag-page')
        ->appends($query_param);

        return view('admin-views.report.inhouse-product-sale', compact('categories', 'category_id', 'products','bags'));
    }
}
