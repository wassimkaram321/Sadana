<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Pharmacy;
use App\CPU\Helpers;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade as PDF;
use Brian2694\Toastr\Facades\Toastr;
use function App\CPU\translate;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{

    function list(Request $request, $status)
    {
        $query_param = [];
        $search = $request['search'];

        $pending = false;

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $pharmacies = User::with('pharmacy')
                ->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "%{$value}%");
                        $q->orWhere('user_type', 'like', "pharmacist");
                    }
                });
            $query_param = ['search' => $request['search']];
        } else {
            $pharmacies = User::with(['pharmacy'])->where('user_type', "pharmacist");
        }

        if ($status != 'Pending') {
            $pharmacies = $pharmacies->where(['is_active' => 1]);
        } else {
            $pending = true;
            $pharmacies = $pharmacies->where(['is_active' => 0]);
        }

        $pharmacies = $pharmacies->latest()->paginate(Helpers::pagination_limit())->appends($query_param);

        return view('admin-views.pharmacy.list', compact('pharmacies', 'search', 'pending', 'status'));
    }

    public function activation(Request $request, $id, $status)
    {
        $User = User::find($id);
        if ($status == 1) {
            $User->is_active = 1;
        } else {
            $User->is_active = 0;
        }
        $User->save();
        return Redirect::back();
    }


    public function vip(Request $request, $id, $status)
    {
        $pharmacy = Pharmacy::find($id);
        if ($status == 1) {
            $pharmacy->vip = 1;
        } else {
            $pharmacy->vip = 0;
        }
        $pharmacy->save();
        return Redirect::back();
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
        $pharmacy = new Pharmacy();
        try {
            $pharmacy->name = $request->name;
            $pharmacy->lat = $request->lat;
            $pharmacy->lan = $request->lan;
            $pharmacy->city = $request->city;
            $pharmacy->region = $request->region;
            $pharmacy->user_id = $request->user_id;
            $user_type = User::where('id', $pharmacy->user_id)->get();
            $pharmacy->user_type = $user_type->user_type;
            $pharmacy->save();
            return response()->json('Pharmacy Created', 200);
        } catch (Exception $ex) {
            return response()->json('Pharmacy Not Created', 200);
        }
    }

    public function show(Pharmacy $pharmacy)
    {
        $pharmacy = Pharmacy::find($pharmacy->id);
    }

    public function edit(Request $request)
    {
        //
        $pharmacy = Pharmacy::find($request->pharmacy_id);
        $pharmacy->name = $request->name;
        $pharmacy->lat = $request->lat;
        $pharmacy->lan = $request->lan;
        $pharmacy->city = $request->city;
        $pharmacy->region = $request->region;
        $pharmacy->user_id = $request->user_id;
        $user_type = User::where('id', $pharmacy->user_id)->get();
        $pharmacy->user_type = $user_type->user_type;

        $pharmacy->save();

        return response()->json('Pharmacy Updated', 200);
    }


    public function update(Request $request, Pharmacy $pharmacy)
    {
        return view('admin-views.pharmacy.edit', compact('pharmacies'));
    }


    public function destroy(Request $request)
    {
        $pharama = Pharmacy::find($request->id);
        $pharama->delete();
    }


    public function bulk_import_index()
    {
        return view('admin-views.pharmacy.bulk-import');
    }


    public function bulk_import_data(Request $request)
    {
        //Toastr::success('Comming soon!');
        // try {
        //     $collections = (new FastExcel)->import($request->file('Customer_file'));
        // } catch (\Exception $exception) {
        //     Toastr::error('You have uploaded a wrong format file, please upload the right file.');
        //     return back();
        // }


        // $data = [];
        // $skip = ['التصنيف', 'ملاحظات', 'thumbnail'];

        // foreach ($collections as $collection) {

        //     foreach ($collection as $key => $value) {
        //         if ($key!="" && $value === "" && !in_array($key, $skip)) {
        //             Toastr::error('Please fill ' . $key . ' fields');
        //             return back();
        //         }
        //     }

        //     if(isset($collection['اسم المستودع']))
        //     {
        //         $store = Store::where('store_name', 'LIKE', '%'.$collection['اسم المستودع'].'%')->get()->first();
        //         if(isset($store))
        //         {
        //               $store_id=$store->id;
        //         }else
        //         {
        //             $NewStore = new Store();
        //             $NewStore->store_name = $collection['اسم المستودع'];
        //             $NewStore->store_image='def.png';
        //             $NewStore->store_status=1;
        //             $NewStore->save();
        //             $store_id=$NewStore->id;
        //         }
        //     }
        //     else{
        //         $store = Store::take(1)->first();
        //         if(isset($store))
        //         {
        //             $store_id=$store->id;
        //         }
        //         else
        //         {
        //             Toastr::error('Please create a store first or Add the store name field to the uploaded file');
        //             return back();
        //         }
        //     }

        //     $category = [];
        //     array_push($category, [
        //         'id' => 9999999,
        //         'position' => 10,
        //     ]);

        //     $brand = Brand::where('name', 'LIKE', '%'.$collection['المجموعة'].'%')->get()->first();
        //     if(isset($brand))
        //     {
        //           $brand_id=$brand->id;
        //     }else
        //     {
        //         $NewBrand = new Brand();
        //         $NewBrand->name = $collection['المجموعة'];
        //         $NewBrand->image='def.png';
        //         $NewBrand->status=1;
        //         $NewBrand->save();
        //         $brand_id=$NewBrand->id;
        //     }

        //     array_push($data, [
        //         'brand_id' => $brand_id,
        //         'name' => $collection['اسم المادة'],
        //         'unit_price' => $collection['السعر'],
        //         'current_stock' => $collection['الكمية'],
        //         'details' => $collection['الملاحظات'],
        //     ]);
        // }
        // DB::table('products')->insert($data);
        // Toastr::success(count($data) . ' - Products imported successfully!');
        // return back();
    }


    public function bulk_export_data()
    {
        // $products = Product::where(['added_by' => 'admin'])->get();
        // $storage = [];
        // foreach ($products as $item) {
        //     $category_id = 0;
        //     $sub_category_id = 0;
        //     $sub_sub_category_id = 0;
        //     foreach (json_decode($item->category_ids, true) as $category) {
        //         if ($category['position'] == 1) {
        //             $category_id = $category['id'];
        //         } else if ($category['position'] == 2) {
        //             $sub_category_id = $category['id'];
        //         } else if ($category['position'] == 3) {
        //             $sub_sub_category_id = $category['id'];
        //         }
        //     }
        //     $brand = Brand::findOrFail($item->brand_id);
        //     $storage[] = [

        //     ];
        // }
        // return (new FastExcel($storage))->download('inhouse_products.xlsx');

    }

    public function  generate_excel(Request $request)
    {

        $user = User::with(['pharmacy'])->where('id', $request->id)->get()->first();
        $storage = [];
        if ($user->is_active == 1)
            $cusStatus = "قيد العمل";
        else
            $cusStatus = "مغلق";

        if ($user->user_type == "pharmacist")
            $work = "صيدلية";
        else
            $work = "";


        $sales_id  = DB::select('select sales_id from sales_pharmacy where pharmacy_id = ?', [$user->pharmacy->id]);
        $arr = array();
        foreach ($sales_id as $idx) {
            array_push($arr, $idx->sales_id);
        }

        if (isset($arr[0]))
            $salesMan1 = User::where('id', $arr[0])->get()->first();

        if (isset($arr[1]))
            $salesMan2 = User::where('id', $arr[1])->get()->first();


        if (isset($salesMan1))
            $salesManName1 = $salesMan1->f_name . '' . $salesMan1->l_name;
        else
            $salesManName1 = "";

        if (isset($salesMan2))
            $salesManName2 = $salesMan2->f_name . '' . $salesMan2->l_name;
        else
            $salesManName2 = "";


        $storage[] = [
            'الاسم' =>  $user->pharmacy->name,
            'التصنيف' => "New",
            'هاتف 1' => $user->pharmacy->land_number,
            'هاتف 2' => "",
            'خليوي' => $user->phone,
            'ملاحظات' => "",
            'الكتلة' => $user->country,
            'المدينة' => $user->pharmacy->city,
            'المنطقة' => $user->pharmacy->region,
            'العنوان' => $user->street_address,
            'وضع الزبون' => $cusStatus,
            'العمل' => $work,
            'A مندوب فريق ' => $salesManName1,
            'B مندوب فريق ' => $salesManName2,
        ];


        $xlsx = ".xlsx";
        $result = $user->name . '-' . now() . '' . $xlsx;
        return (new FastExcel($storage))->download($result);
    }
}
