<?php

namespace App\CPU;

use App\Model\Admin;
use App\Model\AdminWallet;
use App\Model\Cart;
use App\Model\CartShipping;
use App\Model\Order;
use App\Model\Brand;
use App\Model\OrderDetail;
use App\Model\OrderTransaction;
use App\Pharmacy;
use App\Model\Product;
use App\Model\ProductPoint;
use App\Model\Seller;
use App\Model\OrderAlameen;
use App\Model\PharmaciesPoints;
use App\Model\SellerWallet;
use App\Model\OrdersPoints;
use App\Model\Bag;
use App\Model\BagsOrdersDetails;
use App\Model\BagProduct;
use App\Model\ShippingType;
use App\Model\ShippingAddress;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class OrderManager
{
    public static function track_order($order_id)
    {
        $order = Order::where(['id' => $order_id])->first();
        $order['billing_address_data'] = json_decode($order['billing_address_data']);
        $order['shipping_address_data'] = json_decode($order['shipping_address_data']);
        return $order;
    }

    public static function gen_unique_id()
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }

    public static function order_summary($order)
    {
        $sub_total = 0;
        $total_tax = 0;
        $total_discount_on_product = 0;
        $sub_total_bag = 0;
        $total_tax_bag = 0;
        $total_discount_on_product_bag = 0;

        $bagOrderDetails=BagsOrdersDetails::where('order_id','=',$order->id)->get();

        foreach ($bagOrderDetails as $detail) {
            $sub_total_bag += ($detail->bag_qty*$detail->bag_price);
            $total_tax_bag += $detail->bag_tax;
            $total_discount_on_product_bag += $detail->bag_discount;
        }


        foreach ($order->details as $key => $detail) {
            $sub_total += ($detail->price * $detail->qty);
            $total_tax += $detail->tax ;
            $total_discount_on_product += $detail->discount ;
        }

        $sub_total = $sub_total+$sub_total_bag;
        $total_tax = $total_tax+$total_tax_bag;
        $total_discount_on_product = $total_discount_on_product+$total_discount_on_product_bag;

        $total_shipping_cost = $order['shipping_cost'];
        return [
            'subtotal' => $sub_total,
            'total_tax' => $total_tax,
            'total_discount_on_product' => $total_discount_on_product,
            'total_shipping_cost' => $total_shipping_cost,
        ];
    }

    public static function stock_update_on_order_status_change($order, $status)
    {
        if ($status == 'returned' || $status == 'failed' || $status == 'canceled') {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 1) {
                    $product = Product::find($detail['product_id']);
                    $type = $detail['variant'];
                    $var_store = [];
                    foreach (json_decode($product['variation'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['qty'] += $detail['qty'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variation' => json_encode($var_store),
                        'current_stock' => $product['current_stock'] + $detail['qty'] +  $detail['total_qty'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 0
                    ]);
                }
            }
            $orderAlameen = OrderAlameen::where('order_id', '=', $order->id)->update(["status" => $status]);
        } else {
            foreach ($order->details as $detail) {
                if ($detail['is_stock_decreased'] == 0) {
                    $product = Product::find($detail['product_id']);

                    //check stock
                    /*foreach ($order->details as $c) {
                        $product = Product::find($c['product_id']);
                        $type = $detail['variant'];
                        foreach (json_decode($product['variation'], true) as $var) {
                            if ($type == $var['type'] && $var['qty'] < $c['qty']) {
                                Toastr::error('Stock is insufficient!');
                                return back();
                            }
                        }
                    }*/

                    $type = $detail['variant'];
                    $var_store = [];
                    foreach (json_decode($product['variation'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['qty'] -= $detail['qty'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variation' => json_encode($var_store),
                        'current_stock' => $product['current_stock'] - $detail['qty'] - $detail['total_qty'],
                    ]);
                    OrderDetail::where(['id' => $detail['id']])->update([
                        'is_stock_decreased' => 1
                    ]);
                }
            }
            $orderAlameen = OrderAlameen::where('order_id', '=', $order->id)->update(["status" => $status]);
        }
    }


    public static function stock_update_on_order_delete_change($detail, $order_id)
    {
        $product = Product::find($detail['product_id']);
        $type = $detail['variant'];
        $var_store = [];
        foreach (json_decode($product['variation'], true) as $var) {
            if ($type == $var['type']) {
                $var['qty'] += $detail['qty'];
            }
            array_push($var_store, $var);
        }
        Product::where(['id' => $product['id']])->update([
            'variation' => json_encode($var_store),
            'current_stock' => $product['current_stock'] + $detail['qty'],
        ]);
        $order = Order::where('id', '=', $order_id)->get()->first();
        $order->order_amount = CartManager::order_grand_total($order_id, $detail['product_id'], "delete") - $order->discount;
        $order->save();
    }

    public static function stock_update_on_order_edit_change($detail, $order_id, $qtyNew)
    {

        $product = Product::find($detail['product_id']);
        $type = $detail['variant'];
        $var_store = [];
        foreach (json_decode($product['variation'], true) as $var) {
            if ($type == $var['type']) {
                $var['qty'] += $qtyNew;
            }
            array_push($var_store, $var);
        }
        if ($qtyNew > $detail['qty'])
            $Q = $detail['qty'] - $qtyNew;
        elseif ($qtyNew == $detail['qty'])
            $Q = $detail['qty'];
        elseif ($qtyNew < $detail['qty'])
            $Q = $detail['qty'] - $qtyNew;

        Product::where(['id' => $product['id']])->update([
            'variation' => json_encode($var_store),
            'current_stock' => $product['current_stock'] + $Q,
        ]);
        $ordersDetails = OrderDetail::where('order_id', '=', $order_id)
            ->where('product_id', '=', $detail['product_id'])
            ->get()
            ->first();
        $ordersDetails->qty = $qtyNew;

        $total_qty = 0;
        $offerType = 'no offer';

        if ($product->q_featured_offer != 0 &&  $product->featured_offer != 0) {
            $total_qty = ((int)($qtyNew / $product->q_featured_offer)) * $product->featured_offer;
            $offerType = 'featured';
        }
        if ($total_qty == 0) {
            if ($product->q_normal_offer != 0 && $product->normal_offer != 0) {
                $total_qty = ((int)($qtyNew / $product->q_normal_offer)) * $product->normal_offer;
                $offerType = 'normal';
            }
        }
        $ordersDetails->total_qty = $total_qty;
        $ordersDetails->offerType = $offerType;
        $ordersDetails->save();

        $order = Order::where('id', '=', $order_id)->get()->first();
        $order->order_amount = CartManager::order_grand_total($order_id, $detail['product_id'], "edit") - $order->discount;
        $order->save();
    }

    public static function wallet_manage_on_order_status_change($order, $received_by)
    {
        $order = Order::find($order['id']);
        $order_summary = OrderManager::order_summary($order);
        $order_amount = $order_summary['subtotal'] - $order_summary['total_discount_on_product'] - $order['discount_amount'];
        $commission = Helpers::sales_commission($order);
        $shipping_model = Helpers::get_business_settings('shipping_method');

        if (AdminWallet::where('admin_id', 1)->first() == false) {
            DB::table('admin_wallets')->insert([
                'admin_id' => 1,
                'withdrawn' => 0,
                'commission_earned' => 0,
                'inhouse_earning' => 0,
                'delivery_charge_earned' => 0,
                'pending_amount' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        if (SellerWallet::where('seller_id', $order['seller_id'])->first() == false) {
            DB::table('seller_wallets')->insert([
                'seller_id' => $order['seller_id'],
                'withdrawn' => 0,
                'commission_given' => 0,
                'total_earning' => 0,
                'pending_withdraw' => 0,
                'delivery_charge_earned' => 0,
                'collected_cash' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($order['payment_method'] == 'cash_on_delivery') {
            DB::table('order_transactions')->insert([
                'transaction_id' => OrderManager::gen_unique_id(),
                'customer_id' => $order['customer_id'],
                'seller_id' => $order['seller_id'],
                'seller_is' => $order['seller_is'],
                'order_id' => $order['id'],
                'order_amount' => $order_amount,
                'seller_amount' => $order_amount - $commission,
                'admin_commission' => $commission,
                'received_by' => $received_by,
                'status' => 'disburse',
                'delivery_charge' => $order['shipping_cost'],
                'tax' => $order_summary['total_tax'],
                'delivered_by' => $received_by,
                'payment_method' => $order['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = AdminWallet::where('admin_id', 1)->first();
            $wallet->commission_earned += $commission;
            if ($shipping_model == 'inhouse_shipping') {
                $wallet->delivery_charge_earned += $order['shipping_cost'];
            }
            $wallet->save();

            if ($order['seller_is'] == 'admin') {
                $wallet = AdminWallet::where('admin_id', 1)->first();
                $wallet->inhouse_earning += $order_amount;
                if ($shipping_model == 'sellerwise_shipping') {
                    $wallet->delivery_charge_earned += $order['shipping_cost'];
                }
                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            } else {
                $wallet = SellerWallet::where('seller_id', $order['seller_id'])->first();
                $wallet->commission_given += $commission;
                $wallet->total_tax_collected += $order_summary['total_tax'];

                if ($shipping_model == 'sellerwise_shipping') {
                    $wallet->delivery_charge_earned += $order['shipping_cost'];
                    $wallet->collected_cash += $order['order_amount']; //total order amount
                } else {
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'];
                }

                $wallet->save();
            }
        } else {
            $transaction = OrderTransaction::where(['order_id' => $order['id']])->first();
            $transaction->status = 'disburse';
            $transaction->save();

            $wallet = AdminWallet::where('admin_id', 1)->first();
            $wallet->commission_earned += $commission;
            $wallet->pending_amount -= $order['order_amount'];
            if ($shipping_model == 'inhouse_shipping') {
                $wallet->delivery_charge_earned += $order['shipping_cost'];
            }
            $wallet->save();

            if ($order['seller_is'] == 'admin') {
                $wallet = AdminWallet::where('admin_id', 1)->first();
                $wallet->inhouse_earning += $order_amount;
                if ($shipping_model == 'sellerwise_shipping') {
                    $wallet->delivery_charge_earned += $order['shipping_cost'];
                }
                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            } else {
                $wallet = SellerWallet::where('seller_id', $order['seller_id'])->first();
                $wallet->commission_given += $commission;

                if ($shipping_model == 'sellerwise_shipping') {
                    $wallet->delivery_charge_earned += $order['shipping_cost'];
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'] + $order['shipping_cost'];
                } else {
                    $wallet->total_earning += ($order_amount - $commission) + $order_summary['total_tax'];
                }

                $wallet->total_tax_collected += $order_summary['total_tax'];
                $wallet->save();
            }
        }
    }

    public static function generate_order($data)
    {
        $myArray = array();

        $order_id = 100000 + Order::all()->count() + 1;
        if (Order::find($order_id)) {
            $order_id = Order::orderBy('id', 'DESC')->first()->id + 1;
        }
        $address_id = session('address_id') ? session('address_id') : null;
        $billing_address_id = session('billing_address_id') ? session('billing_address_id') : null;
        $coupon_code = session()->has('coupon_code') ? session('coupon_code') : 0;
        $discount = session()->has('coupon_discount') ? session('coupon_discount') : 0;
        $order_note = session()->has('order_note') ? session('order_note') : null;

        $req = array_key_exists('request', $data) ? $data['request'] : null;
        if ($req != null) {
            if (session()->has('coupon_code') == false) {
                $coupon_code = $req->has('coupon_code') ? $req['coupon_code'] : null;
                $discount = $req->has('coupon_code') ? Helpers::coupon_discount($req) : $discount;
            }
            if (session()->has('address_id') == false) {
                $address_id = $req->has('address_id') ? $req['address_id'] : null;
            }
        }
        $user = Helpers::get_customer($req);

        if ($discount > 0) {
            $discount = round($discount / count(CartManager::get_cart_group_ids($req)), 2);
        }

        $cart_group_id = $data['cart_group_id'];
        $seller_data = Cart::where(['cart_group_id' => $cart_group_id])->first();

        $shipping_method = CartShipping::where(['cart_group_id' => $cart_group_id])->first();
        if (isset($shipping_method)) {
            $shipping_method_id = $shipping_method->shipping_method_id;
        } else {
            $shipping_method_id = 0;
        }

        $shipping_model = Helpers::get_business_settings('shipping_method');
        if ($shipping_model == 'inhouse_shipping') {
            $admin_shipping = ShippingType::where('seller_id', 0)->first();
            $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
        } else {
            if ($seller_data->seller_is == 'admin') {
                $admin_shipping = ShippingType::where('seller_id', 0)->first();
                $shipping_type = isset($admin_shipping) == true ? $admin_shipping->shipping_type : 'order_wise';
            } else {
                $seller_shipping = ShippingType::where('seller_id', $seller_data->seller_id)->first();
                $shipping_type = isset($seller_shipping) == true ? $seller_shipping->shipping_type : 'order_wise';
            }
        }

        $or = [
            'id' => $order_id,
            'verification_code' => rand(100000, 999999),
            'customer_id' => $user->id,
            'seller_id' => $seller_data->seller_id,
            'seller_is' => $seller_data->seller_is,
            'customer_type' => $user->user_type,        //MMMMMM
            'payment_status' => $data['payment_status'],
            'order_status' => $data['order_status'],
            'payment_method' => $data['payment_method'],
            'transaction_ref' => $data['transaction_ref'],
            'order_group_id' => $data['order_group_id'],
            'discount_amount' => $discount,
            'discount_type' => $discount == 0 ? null : 'coupon_discount',
            'coupon_code' => $coupon_code,
            'order_amount' => CartManager::cart_grand_total($cart_group_id) - $discount,
            'shipping_address' => $address_id,
            'shipping_address_data' => ShippingAddress::find($address_id),
            'billing_address' => $billing_address_id,
            'billing_address_data' => ShippingAddress::find($billing_address_id),
            'shipping_cost' => CartManager::get_shipping_cost($data['cart_group_id']),
            'shipping_method_id' => $shipping_method_id,
            'shipping_type' => $shipping_type,
            'created_at' => now(),
            'updated_at' => now(),
            'order_note' => $order_note
        ];

        $order_id = DB::table('orders')->insertGetId($or);

        foreach (CartManager::get_cart($data['cart_group_id']) as $c) {
            if ($c->order_type == "bag") {

                $bagProducts = BagProduct::where(['bag_id' => $c['product_id']])->get();

                foreach ($bagProducts as $ccc) {
                    $p = Product::whereid($ccc->product_id)->first();
                    $b = Brand::whereid($p->brand_id)->first();
                    $ccc['product_name'] = $p->name;
                    $ccc['brand_name'] = $b->name;
                }

                $or_dd = [
                    'order_id' => $order_id,
                    'bag_id' => $c['product_id'],
                    'seller_id' => $c['seller_id'],
                    'bag_details' => json_encode($bagProducts, true),
                    'bag_qty' => $c['quantity'],
                    'bag_price' => $c['price'],
                    'bag_tax' => $c['tax'] * $c['quantity'],
                    'bag_discount' => $c['discount'] * $c['quantity'],
                    'delivery_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'is_stock_decreased' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];


                foreach ($bagProducts as $bagProduct) {
                    $bagProductId = $bagProduct['product_id'];
                    $bagProductQty = $bagProduct['product_count'];
                    $product = Product::where(['id' => $bagProductId])->first();
                    Product::where(['id' => $bagProductId])->update([
                        'current_stock' => $product['current_stock'] - ($bagProductQty * $c['quantity'])
                    ]);
                    $product_d = [
                        'product_id' => $product->num_id,
                        'qty' => ($bagProductQty * $c['quantity']),
                        'price' => $bagProduct['product_total_price'],
                        'q_gift' => 0,
                    ];
                    array_push($myArray, $product_d);
                }
                DB::table('bags_orders_details')->insert($or_dd);
            } else {

                $product = Product::where(['id' => $c['product_id']])->first();

                $total_qty = 0;
                $offerType = 'no offer';

                if ($product->q_featured_offer != 0 &&  $product->featured_offer != 0) {
                    $total_qty = ((int)($c['quantity'] / $product->q_featured_offer)) * $product->featured_offer;
                    $offerType = 'featured';
                }
                if ($total_qty == 0) {
                    if ($product->q_normal_offer != 0 && $product->normal_offer != 0) {
                        $total_qty = ((int)($c['quantity'] / $product->q_normal_offer)) * $product->normal_offer;
                        $offerType = 'normal';
                    }
                }

                $or_d = [
                    'order_id' => $order_id,
                    'product_id' => $c['product_id'],
                    'seller_id' => $c['seller_id'],
                    'product_details' => $product,
                    'qty' => $c['quantity'],
                    'total_qty' => $total_qty,
                    'offerType' => $offerType,
                    'price' => $c['price'],
                    'tax' => $c['tax'] * $c['quantity'],
                    'discount' => $c['discount'] * $c['quantity'],
                    'discount_type' => 'discount_on_product',
                    'variant' => $c['variant'],
                    'variation' => $c['variations'],
                    'delivery_status' => 'pending',
                    'shipping_method_id' => null,
                    'payment_status' => 'unpaid',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $product_d = [
                    'product_id' => $product->num_id,
                    'qty' => $c['quantity'],
                    'price' => $c['price'],
                    'q_gift' => $total_qty,
                ];
                array_push($myArray, $product_d);


                if ($c['variant'] != null) {
                    $type = $c['variant'];
                    $var_store = [];
                    foreach (json_decode($product['variation'], true) as $var) {
                        if ($type == $var['type']) {
                            $var['qty'] -= $c['quantity'];
                        }
                        array_push($var_store, $var);
                    }
                    Product::where(['id' => $product['id']])->update([
                        'variation' => json_encode($var_store),
                    ]);
                }

                Product::where(['id' => $product['id']])->update([
                    'current_stock' => $product['current_stock'] - $c['quantity'] - $total_qty
                ]);

                DB::table('order_details')->insert($or_d);
            }
        }

        if ($user->user_type == "pharmacist") {
            $pharmacy = Pharmacy::where('user_id', '=', $user['id'])->get()->first();
            $userR = User::where('id', '=', $user['id'])->get()->first();
            $orderAlameen = new OrderAlameen;
            $orderAlameen->order_id = $order_id;
            $orderAlameen->pharmacy_id = $userR->pharmacy_id;
            $orderAlameen->pharmacy_name = $pharmacy->name;
            $orderAlameen->product_details = json_encode($myArray);
            $orderAlameen->status = "pending";
            $orderAlameen->save();
        } else {
            //salesman
        }


        if ($or['payment_method'] != 'cash_on_delivery') {
            $order = Order::find($order_id);
            $order_summary = OrderManager::order_summary($order);
            $order_amount = $order_summary['subtotal'] - $order_summary['total_discount_on_product'] - $order['discount'];
            $commission = Helpers::sales_commission($order);

            DB::table('order_transactions')->insert([
                'transaction_id' => OrderManager::gen_unique_id(),
                'customer_id' => $order['customer_id'],
                'seller_id' => $order['seller_id'],
                'seller_is' => $order['seller_is'],
                'order_id' => $order_id,
                'order_amount' => $order_amount,
                'seller_amount' => $order_amount - $commission,
                'admin_commission' => $commission,
                'received_by' => 'admin',
                'status' => 'hold',
                'delivery_charge' => $order['shipping_cost'],
                'tax' => $order_summary['total_tax'],
                'delivered_by' => 'admin',
                'payment_method' => $or['payment_method'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (AdminWallet::where('admin_id', 1)->first() == false) {
                DB::table('admin_wallets')->insert([
                    'admin_id' => 1,
                    'withdrawn' => 0,
                    'commission_earned' => 0,
                    'inhouse_earning' => 0,
                    'delivery_charge_earned' => 0,
                    'pending_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('admin_wallets')->where('admin_id', $order['seller_id'])->increment('pending_amount', $order['order_amount']);
        }

        if ($seller_data->seller_is == 'admin') {
            $seller = Admin::find($seller_data->seller_id);
        } else {
            $seller = Seller::find($seller_data->seller_id);
        }

        try {
            $fcm_token = $user->cm_firebase_token;
            $seller_fcm_token = $seller->cm_firebase_token;
            if ($data['payment_method'] != 'cash_on_delivery') {
                $value = Helpers::order_status_update_message('confirmed');
            } else {
                $value = Helpers::order_status_update_message('pending');
            }

            if ($value) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order_id,
                    'image' => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
                Helpers::send_push_notif_to_device($seller_fcm_token, $data);
            }

            Mail::to($user->email)->send(new \App\Mail\OrderPlaced($order_id));
            // if ($order['seller_is'] == 'seller') {
            //     $seller = Seller::where(['id' => $seller_data->seller_id])->first();
            // } else {
            //     $seller = Admin::where(['admin_role_id' => 1])->first();
            // }
            Mail::to($seller->email)->send(new \App\Mail\OrderReceivedNotifySeller($order_id));
        } catch (\Exception $exception) {
        }

        return $order_id;
    }


    public function products_points($pharmacy_id, $products)
    {
        # code...
        $points = 0;
        $productpoint = ProductPoint::wheretype('product')->get();
        foreach ($productpoint as $p) {

            foreach ($products as $product) {

                $idx = json_decode($p->type_id);
                if (in_array($product->id, $idx)) {
                    $points = $points + $p->points;
                }
            }
        }
        if ($points != 0) {
            $pharmacy = PharmaciesPoints::where('pharmacy_id', $pharmacy_id)->first();
            if (isset($pharmacy)) {
                $pharmacy->points = $pharmacy->points + $points;
                $pharmacy->save();
            } else {
                $pharmacy_points = new PharmaciesPoints();
                $pharmacy_points->pharmacy_id = $pharmacy_id;
                $pharmacy_points->points = $points;
                $pharmacy_points->save();
            }
        }
        return $points;
    }


    public function bags_points($pharmacy_id, $bags)
    {
        # code...
        $points = 0;
        $productpoint = ProductPoint::wheretype('bag')->get();
        foreach ($productpoint as $p) {

            foreach ($bags as $product) {

                $idx = json_decode($p->type_id);
                if (in_array($product->id, $idx)) {
                    $points = $points + $p->points;
                }
            }
        }
        if ($points != 0) {
            $pharmacy = PharmaciesPoints::where('pharmacy_id', $pharmacy_id)->first();
            if (isset($pharmacy)) {
                $pharmacy->points = $pharmacy->points + $points;
                $pharmacy->save();
            } else {
                $pharmacy_points = new PharmaciesPoints();
                $pharmacy_points->pharmacy_id = $pharmacy_id;
                $pharmacy_points->points = $points;
                $pharmacy_points->save();
            }
        }
        return $points;
    }


    public function order_points($pharmacy_id, $order_total_price)
    {
        # code...
        $points = 0;
        $orderpoint = OrdersPoints::get();
        foreach ($orderpoint as $p) {


            if ($order_total_price >= $p->points) {
                $points = $points + $p->points;
            }
        }
        if ($points != 0) {
            $pharmacy = PharmaciesPoints::where('pharmacy_id', $pharmacy_id)->first();
            if (isset($pharmacy)) {
                $pharmacy->points = $pharmacy->points + $points;
                $pharmacy->save();
            } else {
                $pharmacy_points = new PharmaciesPoints();
                $pharmacy_points->pharmacy_id = $pharmacy_id;
                $pharmacy_points->points = $points;
                $pharmacy_points->save();
            }
        }
        return $points;
    }


    public static function stock_update_on_bag_order_status_change($order, $status)
    {
        if ($status == 'returned' || $status == 'failed' || $status == 'canceled') {

            $bagDetails = BagsOrdersDetails::where('order_id', '=', $order->id)->get();

            foreach ($bagDetails as $detail) {
                if ($detail['is_stock_decreased'] == 1) {

                    $bagProducts = json_decode($detail->bag_details,true);

                    foreach ($bagProducts as $bagProduct) {

                        $product = Product::where('id','=',$bagProduct['product_id'])->get()->first();

                        Product::where(['id' => $product['id']])->update([
                            'current_stock' => $product['current_stock'] + $bagProduct['product_count'] * $detail['bag_qty'],
                        ]);

                        BagsOrdersDetails::where(['id' => $detail['id']])->update([
                            'is_stock_decreased' => 0
                        ]);
                    }
                }
            }
        } else {
            $bagDetails = BagsOrdersDetails::where('order_id', '=', $order->id)->get();

            foreach ($bagDetails as $detail) {
                if ($detail['is_stock_decreased'] == 0) {
                    $bagProducts = json_decode($detail->bag_details,true);
                    foreach ($bagProducts as $bagProduct) {

                        $product = Product::where('id','=',$bagProduct['product_id'])->get()->first();
                        Product::where(['id' => $product['id']])->update([
                            'current_stock' => $product['current_stock'] - $bagProduct['product_count'] * $detail['bag_qty'],
                        ]);
                        BagsOrdersDetails::where(['id' => $detail['id']])->update([
                            'is_stock_decreased' => 1
                        ]);
                    }
                }
            }
        }
    }


}
