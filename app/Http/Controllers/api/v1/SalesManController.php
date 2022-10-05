<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\CPU\ProductManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Pharmacy;
use App\Model\WorkPlan;
use App\User;
use Illuminate\Support\Facades\DB;

class SalesManController extends Controller
{
    public function get_pharmacies(Request $request)
    {

        $id=$request->user()->id;
        $pharma_ids = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?', [$id]);
        $arr = array();
        foreach ($pharma_ids as $idx) {
            array_push($arr, $idx->pharmacy_id);
        }

        $pharmacies = Pharmacy::join("users", "users.id", "=", "pharmacies.user_id")
            ->whereIn("pharmacies.id", $arr)
            ->get([
                'pharmacies.id as pharmacy_id','pharmacies.name','pharmacies.lat','pharmacies.lan','pharmacies.city','pharmacies.region as area','pharmacies.user_type_id as user_type'
                ,'pharmacies.from','pharmacies.to','pharmacies.Address','pharmacies.land_number','users.phone as phone'
            ]);

        return response()->json($pharmacies, 200);
    }

    public function get_work_plans(Request $request)
    {
        $id=$request->user()->id;
        $plans= WorkPlan::where(['saler_id' => $id])->where('status_plan','=',1)->get();

        foreach($plans as $plan)
        {
            $pharmaciesSelectedArray = json_decode($plan['pharmacies'], true);
            $pharmacies = Pharmacy::whereIn('id', $pharmaciesSelectedArray)->get([
                'id','name','lat','lan','city','region','user_type_id as user_type','vip','from','to',
                'Address','land_number'
            ]);
            $plan['pharmacies'] = $pharmacies;
        }
       return response()->json($plans, 200);
    }


}
