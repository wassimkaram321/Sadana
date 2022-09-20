<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\City;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;

class CityController extends Controller
{

    public function city_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $br = City::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('city_name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $br = new City();
        }
        $br = $br->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.city.list', compact('br', 'search'));
    }

    //Done
    public function city_store(Request $request)
    {
        $city = new City;
        $city->city_name = $request->city_name;
        $city->city_status = 1;
        $city->save();
        Toastr::success('city added successfully!');
        return back();
    }

    //Done
    public function city_delete(Request $request)
    {
        $city = City::find($request->id);
        $city->delete();
        return response()->json();
    }

    //Done
    public function city_status_update(Request $request)
    {
        $city = City::where(['id' => $request['id']])->get()->first();
        $success = 1;
        if ($request['status'] == 1) {
            $city->city_status = $request['status'];
        } else {
            $city->city_status = $request['status'];
        }
        $city->save();
        return response()->json([
            'success' => $success,
        ], 200);
    }
}
