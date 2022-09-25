<?php

namespace App\Http\Controllers\Admin;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Group;
use App\Model\Area;
use App\Model\City;
use App\Pharmacy;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function customer_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $customers = User::with(['orders'])
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    }
                });
            $query_param = ['search' => $request['search']];
        } else {
            $customers = User::with(['orders']);
        }
        $customers = $customers->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.customer.list', compact('customers', 'search'));
    }

    public function status_update(Request $request)
    {
        User::where(['id' => $request['id']])->update([
            'is_active' => $request['status']
        ]);

        DB::table('oauth_access_tokens')
            ->where('user_id', $request['id'])
            ->delete();

        return response()->json([], 200);
    }

    public function view(Request $request, $id)
    {

        $customer = User::find($id);
        if (isset($customer)) {
            $query_param = [];
            $search = $request['search'];
            $orders = Order::where(['customer_id' => $id]);
            if ($request->has('search')) {

                $orders = $orders->where('id', 'like', "%{$search}%");
                $query_param = ['search' => $request['search']];
            }
            $orders = $orders->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
            return view('admin-views.customer.customer-view', compact('customer', 'orders', 'search'));
        }
        Toastr::error('Customer not found!');
        return back();
    }

    public function edit($id){

        $user = User::with(['pharmacy'])->where(['id' => $id])->withoutGlobalScopes()->get()->first();

        $cus_area = Area::where('id',$user->area_id)->get()->first();
        $cus_group = Group::where('id', $cus_area->group_id)->get()->first();
        $cus_city = City::where('id',$cus_group->city_id)->get()->first();

        return view('admin-views.pharmacy.edit', compact('user','cus_area','cus_group','cus_city'));
    }


    public function groups(Request $request,$cityId)
    {
        if(isset($request->cityId))
        {
            $city_id = $request->cityId;
        }else
        {
            $city_id = $cityId;
        }

        $groups = Group::where('city_id',$city_id)->get();
        return response()->json([
            'groups' => $groups
        ]);
    }

    public function areas(Request $request,$groupId)
    {
        if(isset($request->groupId))
        {
            $group_id = $request->groupId;
        }else
        {
            $group_id = $groupId;
        }

        $areas = Area::where('group_id',$group_id)->get();
        return response()->json([
            'areas' => $areas
        ]);
    }


    public function update(Request $request,$id)
    {
        $user = User::where('id',$id)->get()->first();
        $group = Group::where('id',$request->group_id)->get()->first();
        $area = Area::where('id',$request->area_id)->get()->first();
        $city = City::where('id',$request->city_id)->get()->first();

        $user->f_name=$request->f_name;
        $user->l_name=$request->l_name;
        $user->phone=$request->phone;
        $user->email=$request->email;
        $user->street_address=$request->street_address;
        $user->country=$group->group_name;//الكتلة
        $user->city= $city->city_name;//المدينة
        $user->zip=30303;
        $user->area_id=$request->area_id;


        $pharmacy = Pharmacy::where('user_id',$id)->get()->first();

        $pharmacy->lat=$request->lat;
        $pharmacy->lan=$request->lan;
        $pharmacy->city = $city->city_name;
        $pharmacy->region = $area->area_name; //المنطقة
        $pharmacy->name=$request->name;
        $pharmacy->Address=$request->Address;
        $pharmacy->from=$request->from;
        $pharmacy->to=$request->to;
        $pharmacy->land_number=$request->land_number;
        $pharmacy->card_number=$request->card_number;

        $user->save();
        $pharmacy->save();

        Toastr::success('pharmacy updated successfully!');
        return back();
    }



    public function delete($id)
    {
        $customer = User::find($id);
        $p = Pharmacy::where('user_id',$customer->id);
        $p->delete();
        $customer->delete();
        Toastr::success('Customer deleted successfully!');
        return back();
    }
}
