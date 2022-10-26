<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Model\Bonus;
use Illuminate\Http\Request;

class BonusesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        try{
            $bonuses = Bonus::all();
        }
        catch (\Exception $e) {
        }
        return response()->json($bonuses, 200);
    }
    public function unlock_products(Request $request)
    {
        //
        try{
            $bonuses = Bonus::where('master_product_id',$request->main_product_id)->get();
            foreach($bonuses as $b){
                $b->status= 1;
                $b->save();
            }
        }
        catch (\Exception $e) {
        }
        return response()->json($bonuses, 200);
    }
    public function lock(Request $request)
    {
        //
        try{
            $bonuses = Bonus::where('master_product_id',$request->main_product_id)->get();
            foreach($bonuses as $b){
                $b->status= 0;
                $b->save();
            }
        }
        catch (\Exception $e) {
        }
        return response()->json($bonuses, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
    public function edit(Bonus $bonus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bonus $bonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Bonus  $bonus
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bonus $bonus)
    {
        //
    }
}
