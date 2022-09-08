<?php

namespace App\Http\Controllers\Admin;
use App\CPU\Helpers;
use App\CPU\OrderManager;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\DeliveryMan;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\OrderTransaction;
use App\Model\Product;
use App\Model\Seller;
use App\Model\ShippingAddress;
use Barryvdh\DomPDF\Facade as PDF;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use function App\CPU\translate;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Pharmacy;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    //Done
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
                //dd($key);
                $orders = $orders->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('customer_type', 'like', "%{$value}%");
                    }
                });
                $query_param = ['customer_type' => $request['customer_type']];
            }
        }


        $orders = $orders->where('order_type', 'default_type')->orderBy('id', 'desc')->paginate(Helpers::pagination_limit())->appends($query_param);

        return view('admin-views.order.list', compact('orders', 'search'));
    }

    public function details($id)
    {
        $order = Order::with('details', 'shipping', 'seller')->where(['id' => $id])->first();

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

        $status=false;
        if ($order->orderBy_id != null) {

            $pharmacy = User::where('id',$order->orderBy_id)->get()->first();
            $status=true;
            if ($order->order_type == 'default_type') {
                return view('admin-views.order.order-details', compact('pharmacy','status','shipping_address', 'order', 'linked_orders', 'delivery_men', 'customerDetails'));
            } else {
                return view('admin-views.pos.order.order-details', compact('pharmacy','status','order', 'shipping_address'));
            }
        }

        if ($order->order_type == 'default_type') {
            $status=false;
            return view('admin-views.order.order-details', compact('status','shipping_address', 'order', 'linked_orders', 'delivery_men', 'customerDetails'));
        } else {
            return view('admin-views.pos.order.order-details', compact('status','order', 'shipping_address'));
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

        }catch (\Exception $e) {
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
        foreach ($orderDetails as $item) {
            $orderProduct = Product::where('id', $item->product_id)->get()->first();
            $product_details = json_decode($item->product_details);
            $qtyOffers = 0;

            if (isset($orderProduct->q_normal_offer) && $orderProduct->q_normal_offer != 0) {
                $qtyOffers = ((int)($item->qty / $orderProduct->q_normal_offer)) * $orderProduct->normal_offer;
            }

            if (isset($orderProduct->q_featured_offer) &&  $orderProduct->q_featured_offer != 0) {
                $qtyOffers = ((int)($item->qty / $orderProduct->q_featured_offer)) * $orderProduct->featured_offer;
            }
            if (isset($orderProduct->name) && isset($orderProduct->expiry_date)) {
                $storage[] = [
                    'اسم المادة' =>  $orderProduct->name,
                    'كمية المادة' => $item->qty,
                    'بونص المادة' => $qtyOffers,
                    'سعر المادة' => $item->price,
                    'تاريخ انتهاء الصلاحية' => $orderProduct->expiry_date,
                ];
            } else {
                $storage[] = [
                    'اسم المادة' =>  "غير معروف",
                    'كمية المادة' => $item->qty,
                    'بونص المادة' => $qtyOffers,
                    'سعر المادة' => $item->price,
                    'تاريخ انتهاء الصلاحية' => "0000-00-00",
                ];
            }
        }

        $userName = User::where('id', $product_details->user_id)->get()->first();
        $xlsx = ".xlsx";
        if (isset($userName->name)) {
            $result = $userName->name . '' . now() . '' . $xlsx;
        } elseif (isset($orderProduct->name)) {
            $result = $orderProduct->name . '-' . now() . '' . $xlsx;
        } else {
            $result = now() . '' . $xlsx;
        }

        return (new FastExcel($storage))->download($result);
    }
}
