<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Bonus;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use App\Model\Product;
use Brian2694\Toastr\Facades\Toastr;
use App\Model\Translation;
class BounusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $bonuses = Bonus::all();

        $query_param = [];
        $search = $request['search'];
        if ($request->has('search'))
        {
            $key = explode(' ', $request['search']);
            $bonuses = Bonus::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->Where('name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }else{
            $bonuses = new Bonus();
        }
        // $bonuses = Bonus::groupBy('master_product_id')->latest()->paginate(Helpers::pagination_limit());
        // $bonuses1 = Bonus::get()->groupBy('master_product_id');
        $bonuses = Bonus::latest()->paginate(Helpers::pagination_limit());
        $bonuses1 = Bonus::get()->groupBy('master_product_id');
        // dd($bonuses);


        return view('admin-views.bonuses.list',compact('bonuses','bonuses1','search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $products = Product::all();

        return view('admin-views.bonuses.create',compact('products'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // dd($request);
        $request->validate([

            'first_product' => 'required',
            // 'first_product_q' => 'required',
            // 'sec_products_q' => 'required',
            'sec_products' => 'required',
        ], [

            'first_product.required' => 'First Product is required!',
            'first_product_q.required' => 'First Product Quantity  is required!',
            'sec_products_q.required' => 'Second Product  is required!',
            'sec_products.required' => 'Second Product Quantity  is required!!',
        ]);


            $bonus = new Bonus();

            $bonus->master_product_id = json_encode($request->first_product);
            $bonus->master_product_quatity = json_encode($request->main);
            $bonus->salve_product_id = json_encode($request->sec_products);
            $bonus->salve_product_quatity = json_encode($request->form);
            $bonus->save();

        // $bonus = new Bonus();
        // $id = $bonus->id;
        // $ii = 0;
        // foreach($request->sec_products as $sec){
        //     $bonus = new Bonus();
        //     $bonus->id = $id;
        //     $bonus->master_product_id = $request->first_product;
        //     $bonus->master_product_quatity = $request->first_product_q;
        //     $bonus->salve_product_id = $sec;
        //     $bonus->salve_product_quatity = $request->form[$ii];
        //     $ii++;
        //     $bonus->save();
        // }
        Toastr::success('Bonus added successfully!');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function show(Bonus $bonus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function edit(Bonus $bonus,$id)
    {
        //
        $bonuse = Bonus::findOrFail($id);
        return view('admin-views.bonuses.edit',compact('bonuse'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $request->validate([

            'first_product' => 'required',
            'first_product_q' => 'required',
            'sec_products_q' => 'required',
            'sec_products' => 'required',
        ], [

            'first_product.required' => 'First Product is required!',
            'first_product_q.required' => 'First Product Quantity  is required!',
            'sec_products_q.required' => 'Second Product  is required!',
            'sec_products.required' => 'Second Product Quantity  is required!!',
        ]);
        try {
            $bonus = Bonus::findOrFail($id);
            $bonus->master_product_id = $request->first_product;
            $bonus->master_product_quantity = $request->first_product_q;
            $bonus->salve_product_id = $request->sec_products;
            $bonus->slave_product_quantity = $request->sec_products_q;
            $bonus->save();
            Toastr::success('Bonus updated successfully!');
            return redirect()->back();
        } catch (\Throwable $th) {
            Toastr::success('Bonus update Fail!');
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //

        $translation = Translation::where('translationable_type', 'App\Model\Bonus')
        ->where('translationable_id', $request->id);
    $translation->delete();
    $bonus = Bonus::where('id',$request->id)->first();
    $bonus->delete();
    return response()->json();
    }
    public function destroy_sec(Request $request)
    {

        $translation = Translation::where('translationable_type', 'App\Model\Bonus')
        ->where('translationable_id', $request->main);
    $translation->delete();
    $bonus = Bonus::where('id',$request->main)->first();
    //master
    $idx1 = json_decode($bonus->master_product_id);
    $idx2 = json_decode($bonus->master_product_quatity);
    $array1 = [];
    $array2 = [];

      for($ii = 0;$ii<sizeof($idx1);$ii++){
        if($idx1[$ii] != $request->id){
         $array1[] = $idx1[$ii];
         $array2[] = $idx2[$ii];
        }
    }

    $bonus->master_product_id = json_encode($array1);
    $bonus->master_product_quatity = json_encode($array2);
    // $bonus->salve_product_id = json_encode($array11);
    // $bonus->salve_product_quatity = json_encode($array22);
    $bonus->save();



    return response()->json();
    }
    public function get_salve_products(Request $request)
    {
        # code...

        $bonuses1 = Bonus::where('id',$request->id)->get();

        $data = [];

        $ii =0 ;
        foreach($bonuses1 as $b){
            $bonus_name = Product::where('id',$b->salve_product_id)->pluck('name')->first();
            $data[$ii]['id'] =$b->id;
            // $data[$ii]['salve_name']=$bonus_name;
            $idx = json_decode($b->salve_product_id);
            $names = array();
            foreach($idx as $id){
                $name= Product::where('id',$id)->pluck('name')->first();
                array_push($names,$name);
            }
            $data[$ii]['salve_name']=$names;
            $data[$ii]['salve_product_quatity'] =json_decode($b->salve_product_quatity);
            $data[$ii]['salve_product_id'] =json_decode($b->salve_product_id);
            $ii++;
            // $data['id'] =$b->id;
        }
        // dd($data);

        return response()->json($data);

    }
    public function get_main_products(Request $request)
    {
        # code...

        $bonuses1 = Bonus::where('id',$request->id)->get();

        $data = [];

        $ii =0 ;
        foreach($bonuses1 as $b){
            $bonus_name = Product::where('id',$b->master_product_id)->pluck('name')->first();
            $data[$ii]['id'] =$b->id;
            // $data[$ii]['salve_name']=$bonus_name;
            $idx = json_decode($b->master_product_id);
            $names = array();
            foreach($idx as $id){
                $name= Product::where('id',$id)->pluck('name')->first();
                array_push($names,$name);
            }
            $data[$ii]['master_name']=$names;
            $data[$ii]['master_product_quatity'] =json_decode($b->master_product_quatity);
            $data[$ii]['master_product_id'] =json_decode($b->master_product_id);
            $ii++;
            // $data['id'] =$b->id;
        }
        // dd($data);

        return response()->json($data);

    }
}
