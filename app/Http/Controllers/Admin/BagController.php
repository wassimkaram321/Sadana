<?php

namespace App\Http\Controllers\Admin;

use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\CPU\ImageManager;
use App\Http\Controllers\BaseController;
use App\Model\Brand;
use App\Model\Bag;
use App\Model\BagProduct;
use App\Model\Product;
use App\Model\Store;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;
use function App\CPU\translate;
use App\Model\Cart;
use App\Model\Translation;

class BagController extends BaseController
{


    //Done
    public function bag_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $br = bag::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('bag_name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $br = new bag();
        }
        $br = $br->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.bag.list', compact('br', 'search'));
    }


    //Done
    public function bag_edit($id)
    {
        $b = Bag::where(['id' => $id])->withoutGlobalScopes()->first();
        return view('admin-views.bag.edit', compact('b'));
    }


    //Done
    public function bag_update(Request $request, $id)
    {
        $bag = Bag::find($id);
        $bag->bag_name = $request->bag_name;
        if ($request->has('bag_image')) {
            $bag->bag_image = ImageManager::update('bag/', $bag['bag_image'], 'png', $request->file('bag_image'));
        }
        $bag->bag_description = $request->bag_description;
        $bag->end_date = $request->end_date;
        $bag->save();
        Toastr::success('bag updated successfully!');
        return back();
    }


    //Done
    public function bag_delete(Request $request)
    {
        $translation = Translation::where('translationable_type', 'App\Model\Bag')
            ->where('translationable_id', $request->id);
        $translation->delete();
        $bag = Bag::find($request->id);
        ImageManager::delete('brand/' . $bag['bag_image']);
        $bag->delete();
        return response()->json();
    }

    //Done
    public function bag_add_new()
    {
        $br = Bag::latest()->paginate(Helpers::pagination_limit());
        return view('admin-views.bag.add-new', compact('br'));
    }

    //Done
    public function bag_store(Request $request)
    {
        $bag = new Bag;
        $bag->bag_name = $request->bag_name;
        $bag->bag_description = $request->bag_description;
        $bag->end_date = $request->end_date;
        $bag->bag_image = ImageManager::upload('bag/', 'png', $request->file('bag_image'));
        $bag->bag_status = 1;
        $bag->save();
        Toastr::success('store added successfully!');
        return back();
    }


    //Bag produts fun
    //Done
    public function bag_products_list(Request $request, $id)
    {

        $bag_products = BagProduct::join("products", "products.id", "=", "products_bag.product_id")
            ->where("products_bag.bag_id", $id)
            ->get([
                'products_bag.id as id',
                'products.id as product_id', 'products.name', 'products.thumbnail',
                'products_bag.product_count',
                'products_bag.product_price', 'products_bag.product_total_price',
            ]);

        $br = Product::get();
        $users = DB::table('products')
            ->select('id', 'name')
            ->get();
        $bag_id = $id;
        return view('admin-views.bag.bag-product-view', compact('bag_products', 'br', 'bag_id'));
    }



    public function bag_products_store(Request $request, $bag_id)
    {
        $bagProduct = new BagProduct();
        $bagProduct->bag_id = $bag_id;
        $bagProduct->product_count = $request->product_count;
        $bagProduct->product_id = $request->product_id;

        $product = DB::table('products')
            ->select('id', 'name', 'unit_price')
            ->where('id', $request->product_id)
            ->get()->first();

        $bagProduct->product_price =  $product->unit_price;
        $bagProduct->product_total_price = $product->unit_price * $request->product_count;
        $bagProduct->save();

        $price = DB::table('products_bag')->where('bag_id',$bag_id)->sum('product_total_price');
        $bag =Bag::findOrFail($bag_id);
        $bag->total_price_offer=$price;
        $bag->save();
        Toastr::success('Product added successfully!');
        return back();
    }

    public function bag_products_delete(Request $request)
    {
        $bag = BagProduct::findOrFail($request->id);
        $bag->delete();
        Toastr::success('Product deleted successfully!');
    }
}
