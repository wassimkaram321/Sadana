<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\CPU\OrderManager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Model\Bag;
use App\User;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderTransaction;
use App\Model\Product;
use App\Model\Seller;
use App\Model\BagProduct;
use App\Model\BagsOrdersDetails;
use App\Model\ShippingAddress;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use function App\CPU\translate;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Pharmacy;
use Exception;


class OrderController extends Controller
{

    public function list(Request $request, $status)
    {
        $query_param = [];
        $search = $request['search'];

        if (session()->has('show_inhouse_orders') && session('show_inhouse_orders') == 1) {
            $query = Order::whereHas('details', function ($query) {
                $query->whereHas('product', function ($query) {
                    $query->where('added_by', 'admin');
                });
            })->with(['customer']);

            if ($status != 'all') {
                $orders = $query->where(['order_status' => $status]);
            } else {
                $orders = $query;
            }
        } else {
            if ($status != 'all') {
                $orders = Order::with(['customer'])->where(['order_status' => $status]);
            } else {
                $orders = Order::with(['customer']);
            }
        }

        Order::where(['checked' => 0])->update(['checked' => 1]);
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders = $orders->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_ref', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        if ($request->has('customer_type')) {
            if ($request['customer_type'] != 'all') {
                $key = explode(' ', $request['customer_type']);
                $orders = $orders->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('customer_type', 'like', "%{$value}%");
                    }
                });
                $query_param = ['customer_type' => $request['customer_type']];
            }
        }
        $orders = $orders->where('order_type', 'default_type')->orderBy('id', 'desc')->paginate(Helpers::pagination_limit())->appends($query_param);

        foreach($orders as $order)
        {
            if($order->orderBy_id!=0)
            {
                $cus_id=$order->orderBy_id;         //pharamcy
                $details = Pharmacy::where('id', '=', $cus_id)->get()->first();
                $order['pharamcy_name']=$details->name;
            }
            else
            {
                $cus_id=$order->customer_id;       //user
                $details = Pharmacy::where('user_id', '=', $cus_id)->get()->first();
                $order['pharamcy_name']=$details->name;
            }
        }
        return view('admin-views.order.list', compact('orders', 'search'));
    }


    public function details($id)
    {
        $order = Order::with('details', 'shipping', 'seller')->where(['id' => $id])->first();
        $bagsOrder = BagsOrdersDetails::where(['order_id' => $id])->get();

        $count = 0;
        foreach ($bagsOrder as $bagOrder) {
            $bag = Bag::where(['id' => $bagOrder->bag_id])->get()->first();
            $bagProducts = BagProduct::where(['bag_id' => $bagOrder->bag_id])->get();
            foreach ($bagProducts as $bagProduct) {
                if ($bagProduct->is_gift == 1) {
                    $count = $count + $bagProduct->product_count;
                }
            }
            $bagOrder['bag_name'] = $bag->bag_name;
            $bagOrder['total_qty'] = $count;
        }


        $linked_orders = Order::where(['order_group_id' => $order['order_group_id']])
            ->whereNotIn('order_group_id', ['def-order-group'])
            ->whereNotIn('id', [$order['id']])
            ->get();

        $shipping_method = Helpers::get_business_settings('shipping_method');
        $delivery_men = DeliveryMan::where('is_active', 1)->when($order->seller_is == 'admin', function ($query) {
            $query->where(['seller_id' => 0]);
        })->when($order->seller_is == 'seller' && $shipping_method == 'sellerwise_shipping', function ($query) use ($order) {
            $query->where(['seller_id' => $order['seller_id']]);
        })->when($order->seller_is == 'seller' && $shipping_method == 'inhouse_shipping', function ($query) use ($order) {
            $query->where(['seller_id' => 0]);
        })->get();

        $shipping_address = ShippingAddress::find($order->shipping_address);
        $customerDetails = User::where('id', $order->customer_id)->get()->first();

        $status = false;
        if ($order->orderBy_id != null && $order->orderBy_id != 0) {

            $pharmacy = Pharmacy::where('id', $order->orderBy_id)->get()->first();
            $UserPharmacy = User::where('id', $pharmacy->user_id)->get()->first();

            $status = true;
            if ($order->order_type == 'default_type') {
                return view('admin-views.order.order-details', compact('bagsOrder', 'status', 'shipping_address', 'order', 'linked_orders', 'delivery_men', 'pharmacy', 'UserPharmacy'));
            } else {
                return view('admin-views.pos.order.order-details', compact('status', 'order', 'shipping_address', 'pharmacy', 'UserPharmacy'));
            }
        }

        if ($order->order_type == 'default_type') {
            $status = false;
            return view('admin-views.order.order-details', compact('bagsOrder', 'status', 'shipping_address', 'order', 'linked_orders', 'delivery_men'));
        } else {
            return view('admin-views.pos.order.order-details', compact('status', 'order', 'shipping_address'));
        }
    }

    public function add_delivery_man($order_id, $delivery_man_id)
    {
        if ($delivery_man_id == 0) {
            return response()->json([], 401);
        }
        $order = Order::find($order_id);
        /*if($order->order_status == 'delivered' || $order->order_status == 'returned' || $order->order_status == 'failed' || $order->order_status == 'canceled' || $order->order_status == 'scheduled') {
            return response()->json(['status' => false], 200);
        }*/
        $order->delivery_man_id = $delivery_man_id;
        $order->delivery_type = 'self_delivery';
        $order->delivery_service_name = null;
        $order->third_party_delivery_tracking_id = null;
        $order->save();

        $fcm_token = $order->delivery_man->fcm_token;
        $value = Helpers::order_status_update_message('del_assign') . " ID: " . $order['id'];
        try {
            if ($value != null) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {
            Toastr::warning(\App\CPU\translate('Push notification failed for DeliveryMan!'));
        }

        return response()->json(['status' => true], 200);
    }


    public function add_pharmacy_man($order_id, $pharmacy_man_id)
    {
        if ($pharmacy_man_id == 0) {
            return response()->json([], 401);
        }

        try {
            $order = Order::find($order_id);
            $order->orderBy_id = $pharmacy_man_id;
            $order->save();
        } catch (\Exception $e) {
            Toastr::warning(\App\CPU\translate('failed for Pharmacy!'));
            return response()->json([], 401);
        }

        return response()->json(['status' => true], 200);
    }

    public function status(Request $request)
    {
        $order = Order::find($request->id);
        $fcm_token = $order->customer->cm_firebase_token;
        $value = Helpers::order_status_update_message($request->order_status);
        try {
            if ($value) {
                $data = [
                    'title' => translate('Order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token, $data);
            }
        } catch (\Exception $e) {
        }


        try {
            $fcm_token_delivery_man = $order->delivery_man->fcm_token;
            if ($value != null) {
                $data = [
                    'title' => translate('order'),
                    'description' => $value,
                    'order_id' => $order['id'],
                    'image' => '',
                ];
                Helpers::send_push_notif_to_device($fcm_token_delivery_man, $data);
            }
        } catch (\Exception $e) {
        }

        $order->order_status = $request->order_status;
        OrderManager::stock_update_on_order_status_change($order, $request->order_status);
        OrderManager::stock_update_on_bag_order_status_change($order, $request->order_status);
        $order->save();

        $transaction = OrderTransaction::where(['order_id' => $order['id']])->first();
        if (isset($transaction) && $transaction['status'] == 'disburse') {
            return response()->json($request->order_status);
        }

        if ($request->order_status == 'delivered' && $order['seller_id'] != null) {
            OrderManager::wallet_manage_on_order_status_change($order, 'admin');
            OrderDetail::where('order_id', $order->id)->update(
                ['delivery_status' => 'delivered']
            );
            BagsOrdersDetails::where('order_id', $order->id)->update(
                ['delivery_status' => 'delivered']
            );
        }

        return response()->json($request->order_status);
    }

    public function payment_status(Request $request)
    {
        if ($request->ajax()) {
            $order = Order::find($request->id);
            $order->payment_status = $request->payment_status;
            $order->save();
            $data = $request->payment_status;
            return response()->json($data);
        }
    }

    public function generate_invoice($id)
    {
        $order = Order::with('seller')->with('shipping')->with('details')->where('id', $id)->first();
        $seller = Seller::find($order->details->first()->seller_id);
        $data["email"] = $order->customer != null ? $order->customer["email"] : \App\CPU\translate('email_not_found');
        $data["client_name"] = $order->customer != null ? $order->customer["f_name"] . ' ' . $order->customer["l_name"] : \App\CPU\translate('customer_not_found');
        $data["order"] = $order;

        $mpdf_view = \View::make('admin-views.order.invoice')->with('order', $order)->with('seller', $seller);
        Helpers::gen_mpdf($mpdf_view, 'order_invoice_', $order->id);
    }

    public function inhouse_order_filter()
    {
        if (session()->has('show_inhouse_orders') && session('show_inhouse_orders') == 1) {
            session()->put('show_inhouse_orders', 0);
        } else {
            session()->put('show_inhouse_orders', 1);
        }
        return back();
    }


    public function update_deliver_info(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->delivery_type = 'third_party_delivery';
        $order->delivery_service_name = $request->delivery_service_name;
        $order->third_party_delivery_tracking_id = $request->third_party_delivery_tracking_id;
        $order->delivery_man_id = null;
        $order->save();

        Toastr::success(\App\CPU\translate('updated_successfully!'));
        return back();
    }


    public function  generate_excel(Request $request)
    {

        $orderDetails = OrderDetail::where('order_id', $request->order_id)->get();
        $storage = [];
        $user_id = 0;
        foreach ($orderDetails as $item) {
            $orderProduct = Product::where('id', $item->product_id)->get()->first();
            $product_details = json_decode($item->product_details);

            $storage[] = [
                '?????? ????????????' =>  $product_details->name,
                '???????? ????????????' => $item->qty,
                '???????? ????????????' => $item->total_qty,
                '?????? ????????????' => $item->price,
                '?????????? ???????????? ????????????????' => $product_details->expiry_date,
            ];
            $user_id = $product_details->user_id;
        }

        $orderBagsDetails = BagsOrdersDetails::where('order_id', $request->order_id)->get();
        foreach ($orderBagsDetails as $itemBag) {
            $bag_details = json_decode($itemBag->bag_details);


            foreach ($bag_details as $bag_detail) {
                $storage[] = [
                    '?????? ????????????' =>  $bag_detail->product_name,
                    '???????? ????????????' => ($itemBag->bag_qty) * ($bag_detail->product_count),
                    '???????? ????????????' => 0,
                    '?????? ????????????' => $bag_detail->product_price,
                    '?????????? ???????????? ????????????????' => "0000-00-00",
                ];
            }
        }
        $order = Order::where('id', $request->order_id)->get()->first();
        $user = User::where('id', '=', $order->customer_id)->get()->first();
        if ($user->user_type == "pharmacist") {
            $pharmacy = Pharmacy::where('user_id', '=', $user->id)->get()->first();
            $xlsx = ".xlsx";
            $result =  $pharmacy->name . '' . $xlsx;
            return (new FastExcel($storage))->withoutHeaders()->download($result);
        } else {
            $pharmacy = Pharmacy::where('id', '=', $order->orderBy_id)->get()->first();
            $xlsx = ".xlsx";
            $result =  $pharmacy->name . '' . $xlsx;
            return (new FastExcel($storage))->withoutHeaders()->download($result);
        }
    }


    public function edit_order($id)
    {
        try {
            $order = Order::with('details', 'shipping', 'seller')->where(['id' => $id])->first();
            if ($order->order_type == 'default_type') {
                return view('admin-views.order.order-editing', compact('order'));
            } else {
                return back();
            }
        } catch (Exception $e) {
            return back();
        }
    }


    public function delete_product_order(Request $request)
    {
        try {
            if ($request->ajax()) {
                $product = OrderDetail::where('order_id', '=', $request->order_id)
                    ->where('product_id', '=', $request->product_id)->get()->first();
                OrderManager::stock_update_on_order_delete_change($product, $request->order_id);
                $product->delete();
            }

            $product_name = Product::where('id', '=', $request->product_id)->get()->first();
            if (isset($product_name))
                $productName = $product_name->name;
            else
                $productName = "?????? ????????";

            //notification
            $order = Order::find($request->order_id);
            $fcm_token = $order->customer->cm_firebase_token;

            $message = "???? ?????????? ???????????? (" . $productName . ") ?????? ?????????????? (" . $order['id'] . ")";
            try {
                if (true) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $message,
                        'order_id' => $order['id'],
                        'image' => '',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }

            $data = 1;
            return response()->json($data);
        } catch (\Exception $e) {
            $data = 0;
            return response()->json($data);
        }
    }


    public function product_edit_order(Request $request, $id)
    {
        $orderDetail = OrderDetail::where('order_id', '=', $id)
            ->where('product_id', '=', $request->product_id)->get()->first();
        return response()->json([
            'data' => $orderDetail
        ]);
    }


    public function update_order(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'qty' => 'required',
            ]);
            if ($validator->fails()) {
                Toastr::error('Faild updated!');
            }

            $product = OrderDetail::where('order_id', '=', $request->order_id)
                ->where('product_id', '=', $request->product_id)->get()->first();
            OrderManager::stock_update_on_order_edit_change($product, $request->order_id, $request->qty);

            $product_name = Product::where('id', '=', $request->product_id)->get()->first();
            if (isset($product_name))
                $productName = $product_name->name;
            else
                $productName = "?????? ????????";

            //notification
            $order = Order::find($request->order_id);
            $fcm_token = $order->customer->cm_firebase_token;

            $message = "???? ?????????????? ?????? ???????? ???????????? (" . $productName . ") ?????? ?????????????? (" . $order['id'] . ")";
            try {
                if (true) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => $message,
                        'order_id' => $order['id'],
                        'image' => '',
                    ];
                    Helpers::send_push_notif_to_device($fcm_token, $data);
                }
            } catch (\Exception $e) {
            }

            Toastr::success('Quantity updated successfully!');
            return back();
        } catch (\Exception $e) {
            Toastr::error('Faild updated!');
            return back();
        }
    }

    public function generate_excel_all(Request $request)
    {

        $from = session('from_date');
        $to = session('to_date');

        $orderDetails = Order::whereBetween('created_at', [$from, $to])->get();
        $storage = [];

        foreach ($orderDetails as $item) {
            if ($item->customer_type == "pharmacist") {
                $user = User::where('id', $item->customer_id)->get()->first();
                $pharmacy = Pharmacy::where('user_id', $item->customer_id)->get()->first();
            } else {
                $pharmacy = Pharmacy::where('id', $item->orderBy_id)->get()->first();
                $user = User::where('id', $pharmacy->user_id)->get()->first();
            }
            $order_status = translate($item->order_status);
            $order_paid = translate($item->payment_status);
            $storage[] = [
                '?????? ??????????????' => $item->id,
                '?????? ????????????' => $pharmacy->name,
                '?????????? ????????????????' => $item->order_amount,
                '??????????????' => $pharmacy->region,
                '??????????????' => $pharmacy->Address,
                '?????? ????????????' => $user->phone,
                '???????? ??????????????' => $order_status,
                '???????? ??????????' => $order_paid,
                '?????????? ??????????????' => $item->created_at,
                '?????????? ??????????????' => $item->delivery_date,
                '?????? ??????????????' => $pharmacy->card_number,
                '?????? ????????????' => $user->pharmacy_id,
                '?????? ??????????' => $item->Detection_number,
            ];
        }

        $xlsx = ".xlsx";
        $result = '????????????????' . now() . '' . $xlsx;
        return (new FastExcel($storage))->download($result);
    }


    public function show_order_details($order_id)
    {
        $order = Order::where('id', '=', $order_id)->get()->first();
        if($order->orderBy_id!=0)
        {
            $cus_id=$order->orderBy_id;         //pharamcy
            $details = Pharmacy::where('id', '=', $cus_id)->get()->first();
        }
        else
        {
            $cus_id=$order->customer_id;       //user
            $details = Pharmacy::where('user_id', '=', $cus_id)->get()->first();
        }

        return response()->json([
            'data' => $details
        ]);
    }
}
