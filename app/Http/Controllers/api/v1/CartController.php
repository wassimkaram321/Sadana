<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\CartManager;
use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use App\Model\Product;
use App\Model\Bag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function App\CPU\translate;

class CartController extends Controller
{
    public function cart(Request $request)
    {
        $user = Helpers::get_customer($request);
        $cart = Cart::where(['customer_id' => $user->id])->get();
        foreach($cart as $c){

            if($c->order_type!="bag")
            {
                $p = Product::whereid($c->product_id)->first();
                $c['q_normal_offer']=$p->q_normal_offer;
                $c['q_featured_offer']=$p->q_featured_offer  ;
                $c['normal_offer']=$p->normal_offer;
                $c['featured_offer']=$p->featured_offer;
                $c['demand_limit']=(int)$p->demand_limit;
            }
            if($c->order_type=="bag")
            {
                $p = Bag::whereid($c->product_id)->first();
                $c['q_normal_offer']=0;
                $c['q_featured_offer']=0;
                $c['normal_offer']=0;
                $c['featured_offer']=0;
                $c['demand_limit']=(int)$p->demand_limit;
            }

        }
        $cart->map(function ($data) {
            $data['choices'] = json_decode($data['choices']);
            $data['variations'] = json_decode($data['variations']);
            return $data;
        });

        return response()->json($cart, 200);
    }

    public function add_to_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
            'type' => 'required|string',
        ], [
            'id.required' => translate('Product ID is required!'),
            'type.required' => translate('Type is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($request->type == "product") {
            $validator = Validator::make($request->all(), [
                'pure_price' => 'required',
            ], [
                'pure_price.required' => translate('Pure price is required!')
            ]);
            if ($validator->errors()->count() > 0) {
                return response()->json(['errors' => Helpers::error_processor($validator)]);
            }
        }

        $cart = CartManager::add_to_cart($request);
        return response()->json($cart, 200);
    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required',
            'quantity' => 'required',
            'type' => 'required',
        ], [
            'key.required' => translate('Cart key or ID is required!'),
            'type.required' => translate('type key is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if($request->type=="bag")
          $response = CartManager::bag_update_cart_qty($request);
        else
          $response = CartManager::update_cart_qty($request);

        return response()->json($response);
    }



    public function remove_from_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required'
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $user = Helpers::get_customer($request);
        Cart::where(['id' => $request->key, 'customer_id' => $user->id])->delete();
        return response()->json(translate('successfully_removed'));
    }

    public function remove_all_from_cart(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'key' => 'required'
        // ], [
        //     'key.required' => translate('Cart key or ID is required!')
        // ]);

        // if ($validator->errors()->count() > 0) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)]);
        // }

        $user = Helpers::get_customer($request);
        Cart::where(['customer_id' => $user->id])->delete();
        return response()->json(translate('successfully_removed'));
    }
}
