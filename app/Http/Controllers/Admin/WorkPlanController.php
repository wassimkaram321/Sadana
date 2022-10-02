<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\WorkPlan;
use App\Pharmacy;
use App\User;
use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;

class WorkPlanController extends Controller
{

    public function work_plans_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $plans = WorkPlan::where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('saler_name', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        } else {
            $plans = WorkPlan::whereNotIn('id', [-2]);
        }
        $plans = $plans->latest()->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('admin-views.work-plan.list', compact('search', 'plans'));
    }

    public function work_plan_add()
    {
        $salesman = User::where('user_type', '=', "salesman")->get();
        return view('admin-views.work-plan.add', compact('salesman'));
    }

    public function work_plan_store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'begin_date' => 'required',
            'end_date' => 'required',
            'note' => 'required',
            'saler_id' => 'required',
            'pharamcies_ids' => 'required',
        ], [
            'pharamcies_ids.required' => 'pharamcies  is required!',
        ]);

        $pharmacies = json_encode($request->pharamcies_ids);
        $saler = User::where('id', '=', $request->saler_id)->get()->first();
        DB::table('salers_work_plans')->insert([
            'begin_plan' => $request->begin_date,
            'end_plan' => $request->end_date,
            'saler_id' => $request->saler_id,
            'note' => $request->note,
            'saler_name' => $saler->name,
            'pharmacies' => $pharmacies,
            'status_plan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success('Plan added successfully!');
        return back();
    }

    public function work_plan_delete($id)
    {
        $workPlan = WorkPlan::find($id);
        $workPlan->delete();
        Toastr::success(translate('Work plan removed!'));
        return back();
    }

    public function work_plan_edit($id)
    {
        $plan= WorkPlan::where(['id' => $id])->withoutGlobalScopes()->first();
        $salesman = User::where('user_type', '=', "salesman")->get();


        $pharma_id  = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?', [$plan->saler_id]);
        $arr = array();
        foreach ($pharma_id as $idx) {
            array_push($arr, $idx->pharmacy_id);
        }

        $pharmaciesSelectedArray = json_decode($plan->pharmacies, true);
        $pharmacies = Pharmacy::whereIn('id', $arr)->get();

        return view('admin-views.work-plan.edit', compact('plan','salesman','pharmacies','pharmaciesSelectedArray'));
    }

    public function work_plan_update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'begin_date' => 'required',
            'end_date' => 'required',
            'note' => 'required',
            'saler_id' => 'required',
            'pharamcies_ids' => 'required',
        ], [
            'pharamcies_ids.required' => 'pharamcies  is required!',
        ]);

        $pharmacies = json_encode($request->pharamcies_ids);
        $saler = User::where('id', '=', $request->saler_id)->get()->first();

        $plan= WorkPlan::where(['id' => $id])->withoutGlobalScopes()->first();
        $plan->begin_plan=$request->begin_plan;
        $plan->end_plan=$request->end_plan;
        $plan->saler_id=$request->saler_id;
        $plan->note=$request->note;
        $plan->saler_name=$saler->name;
        $plan->pharmacies=$pharmacies;
        $plan->status_plan=1;
        $plan->save();
        Toastr::success('Plan updated successfully!');
        return back();
    }

    public function work_plan_activation($id)
    {
    }


    public function work_plan_pharmacies($saler_id)
    {
        $sm = User::where(['id' => $saler_id])->first();
        $pharma_id  = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?', [$saler_id]);
        $arr = array();
        foreach ($pharma_id as $idx) {
            array_push($arr, $idx->pharmacy_id);
        }
        $pharmacies = Pharmacy::whereIn('id', $arr)->get();

        return response()->json([
            'pharmacies' => $pharmacies
        ]);
    }
}
