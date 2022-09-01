<?php

namespace App\Http\Controllers\Admin;

use App\User;
use function App\CPU\translate;

use App\Http\Controllers\Controller;
use App\Pharmacy;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;

class SalesManController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        $arr = array();
        foreach($pharma_id as $idx){
            array_push($arr,$idx->pharmacy_id);
        }

        $pharmacies = Pharmacy::whereIn('id' ,$arr)->paginate(5);
        if(count($arr) >0){
            $all_pharmacies = Pharmacy::whereNotIn('id',$arr)->get();
        }
        else{
            $all_pharmacies = Pharmacy::all();
        }
        return view('admin-views.sales-man.view', compact('sm','pharmacies','all_pharmacies'));

    }


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


    public function store(Request $request)
    {
        $request->validate([
            'f_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
        ], [
            'f_name.required' => 'First name is required!'
        ]);

        $sm = new User();
        $sm->user_type= 'salesman';
        $sm->name = $request->f_name.' '.$request->l_name;
        $sm->f_name = $request->f_name;
        $sm->l_name = $request->l_name;
        $sm->email = $request->email;
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
        ], [
            'f_name.required' => 'First name is required!'
        ]);

        $sales_man = User::where(['id' => $id, 'user_type' => 'salesman'])->first();
        if (isset($sales_man) && $request['email'] != $sales_man['email']) {
            $request->validate([
                'email' => 'required|unique:users',
            ]);
        }


        $sales_man->user_type = 'salesman';
        $sales_man->f_name = $request->f_name;
        $sales_man->l_name = $request->l_name;
        $sales_man->email = $request->email;
        $sales_man->phone = $request->phone;
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
}
