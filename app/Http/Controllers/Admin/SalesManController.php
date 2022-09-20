<?php

namespace App\Http\Controllers\Admin;

use App\User;
use function App\CPU\translate;
use App\Http\Controllers\Controller;
use App\Pharmacy;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use App\Model\City;
use App\Model\Area;

class SalesManController extends Controller
{

    public function index()
    {
        //
        return view('admin-views.sales-man.index');
    }
    public function list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];
        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $sales_men = User::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('f_name', 'like', "%{$value}%")
                        ->orWhere('l_name', 'like', "%{$value}%")
                        ->orWhere('phone', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $sales_men = new User();
        }

        $sales_men = $sales_men->latest()->where(['user_type' => 'salesman'])->paginate(25)->appends($query_param);
        return view('admin-views.sales-man.list', compact('sales_men', 'search'));
    }



    public function search(Request $request)
    {
        $key = explode(' ', $request['search']);
        $sales_men = User::where(['user_type' => 'salesman'])->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%")
                    ->orWhere('identity_number', 'like', "%{$value}%");
            }
        })->get();
        return response()->json([
            'view' => view('admin-views.sales-man.partials._table', compact('sales_men'))->render()
        ]);
    }



    public function preview($id)
    {
        $sm= User::where(['id' => $id])->first();
        $pharma_id  = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?',[$id]);
        $area_id  = DB::select('select area_id from sales_area where sales_id = ?',[$id]);
        // $city_id  = DB::select('select city_id from sales_city where sales_id = ?',[$id]);

        $arr = array();
        foreach($pharma_id as $idx){
            array_push($arr,$idx->pharmacy_id);
        }

        $arrArea = array();
        foreach($area_id as $idx){
            array_push($arrArea,$idx->area_id);
        }

        // $arrCity = array();
        // foreach($city_id as $idc){
        //     array_push($arrCity,$idc->city_id);
        // }

        $pharmacies = Pharmacy::whereIn('id' ,$arr)->paginate(5);
        $areas = Area::whereIn('id' ,$arrArea)->paginate(5);
        // $cities = City::whereIn('id' ,$arrCity)->paginate(5);

        if(count($arr) >0){
            $all_pharmacies = Pharmacy::whereNotIn('id',$arr)->get();
        }
        else{
            $all_pharmacies = Pharmacy::all();
        }

        if(count($arrArea) >0){
            $all_areas = Area::whereNotIn('id',$arrArea)->get();

            $all_areas_assign = Area::join("cities", "cities.id", "=", "areas.city_id")
            ->where("areas.id", $arrArea)
            ->get();

        }
        else{
            $all_areas = Area::all();
        }

        // if(count($arrCity) >0){
        //     $all_cities = City::whereNotIn('id',$arrCity)->get();
        // }
        // else{
        //     $all_cities = City::all();
        // }

        return view('admin-views.sales-man.view', compact('sm','pharmacies','all_pharmacies','all_areas','all_areas_assign'));
    }



    //Pharmecy
    public function unassign($id){
        DB::delete('delete FROM sales_pharmacy WHERE pharmacy_id = '.$id.' ');
        return redirect()->back();
    }


    public function assign(Request $request , $id){
        $pharmacies = $request->assigned_pharmacies;
        foreach($pharmacies as $pharma){
            $pharmacy = DB::insert('insert into sales_pharmacy (sales_id, pharmacy_id) values (?, ?)', [$id, $pharma]);
        }
        return redirect()->back();
    }




    //Area
    public function unassign_area($id){
        DB::delete('delete FROM sales_pharmacy WHERE pharmacy_id = '.$id.' ');
        return redirect()->back();
    }


    public function assign_area(Request $request , $id){
        $areas_id = $request->assigned_areas;
        foreach($areas_id as $area_id){
            DB::insert('insert into sales_area (sales_id, area_id) values (?, ?)', [$id, $area_id]);
            $users = User::with(['pharmacy'])->where('area_id', $area_id)->where('user_type','pharmacist')->get();
            foreach($users as $user){
                $pharmacy = Pharmacy::where('user_id',$user->id)->get()->first();
                DB::insert('insert into sales_pharmacy (sales_id, pharmacy_id) values (?, ?)', [$id, $pharmacy->id]);
            }
        }
        return redirect()->back();
    }


    //City
    // public function unassign_city($id){
    //     DB::delete('delete FROM sales_pharmacy WHERE pharmacy_id = '.$id.' ');
    //     return redirect()->back();
    // }


    // public function assign_city(Request $request , $id){
    //     $pharmacies = $request->assigned_pharmacies;
    //     foreach($pharmacies as $pharma){
    //         $pharmacy = DB::insert('insert into sales_pharmacy (sales_id, pharmacy_id) values (?, ?)', [$id, $pharma]);
    //     }
    //     return redirect()->back();
    // }









    public function store(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'area_id' => 'required',
            'city_id' => 'required',
        ], [
            'f_name.required' => 'First name is required!'
        ]);

        $area=Area::where('id', $request->area_id)->get()->first();
        $city=City::where('id',$area->city_id)->get()->first();

        $sm = new User();
        $sm->user_type= 'salesman';
        $sm->name = $request->f_name.' '.$request->l_name;
        $sm->f_name = $request->f_name;
        $sm->l_name = $request->l_name;
        $sm->email = $request->email;

        $sm->area_id = $request->area_id;
        $sm->city = $city->city_name;
        $sm->country = "syria";

        $sm->phone = $request->phone;
        $sm->password = bcrypt($request->password);
        $sm->save();

        Toastr::success('Sales-man added successfully!');
        return redirect('admin/sales-man/list');
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
        $sales_man = User::find($id);
        return view('admin-views.sales-man.edit', compact('sales_man'));
    }

    public function update(Request $request, $id)
    {
        //
        $request->validate([
            'f_name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'phone' => 'required|unique:users,phone,'.$id,
            'area_id' => 'required',
            'city_id' => 'required',
        ], [
            'f_name.required' => 'First name is required!'
        ]);

        $sales_man = User::where(['id' => $id, 'user_type' => 'salesman'])->first();
        if (isset($sales_man) && $request['email'] != $sales_man['email']) {
            $request->validate([
                'email' => 'required|unique:users',
            ]);
        }

        $area=Area::where('id', $request->region_id)->get()->first();
        $city=City::where('id',$area->city_id)->get()->first();

        $sales_man->user_type = 'salesman';
        $sales_man->f_name = $request->f_name;
        $sales_man->l_name = $request->l_name;
        $sales_man->email = $request->email;
        $sales_man->phone = $request->phone;

        $sales_man->area_id = $request->area_id;
        $sales_man->city = $city->city_name;

        $sales_man->password = strlen($request->password) > 1 ? bcrypt($request->password) : $sales_man['password'];
        $sales_man->save();

        Toastr::success('Sales-man updated successfully!');
        return redirect('admin/sales-man/list');
    }



    public function destroy(Request $request)
    {
        //
        $sales_man = User::find($request->id);

        $sales_man->delete();
        Toastr::success(translate('Sales-man removed!'));
        return back();
    }


    public function areas(Request $request,$catId)
    {
        if(isset($request->city_id))
        {
            $city_id = $request->city_id;
        }else
        {
            $city_id = $catId;
        }

        $areas = Area::where('city_id',$city_id)->get();
        return response()->json([
            'areas' => $areas
        ]);
    }
}
