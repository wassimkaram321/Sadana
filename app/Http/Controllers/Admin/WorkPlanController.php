<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\WorkPlan;
use App\Model\PlanDetails;
use App\Pharmacy;
use App\User;
use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;
use Illuminate\Support\Facades\Http;
use App\Traits\distanceTrait;

class WorkPlanController extends Controller
{
    use distanceTrait;
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

        if ($request->has('check_all')) {
            $pharma_id  = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?', [$request->saler_id]);
            $arr = array();
            foreach ($pharma_id as $idx) {
                array_push($arr, (string)$idx->pharmacy_id);
            }
            $pharmacies = json_encode($arr);
        } else {
            $pharmacies = json_encode($request->pharamcies_ids);
        }

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
        $plan = WorkPlan::where(['id' => $id])->withoutGlobalScopes()->first();
        $salesman = User::where('user_type', '=', "salesman")->get();


        $pharma_id  = DB::select('select pharmacy_id from sales_pharmacy where sales_id = ?', [$plan->saler_id]);
        $arr = array();
        foreach ($pharma_id as $idx) {
            array_push($arr, $idx->pharmacy_id);
        }

        $pharmaciesSelectedArray = json_decode($plan->pharmacies, true);
        $pharmacies = Pharmacy::whereIn('id', $arr)->get();

        return view('admin-views.work-plan.edit', compact('plan', 'salesman', 'pharmacies', 'pharmaciesSelectedArray'));
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

        $plan = WorkPlan::where(['id' => $id])->withoutGlobalScopes()->first();
        $plan->begin_plan = $request->begin_plan;
        $plan->end_plan = $request->end_plan;
        $plan->saler_id = $request->saler_id;
        $plan->note = $request->note;
        $plan->saler_name = $saler->name;
        $plan->pharmacies = $pharmacies;
        $plan->status_plan = 1;
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


    public function work_plan_details($plan_id, Request $request)
    {
        $search = "";
        try {
            $PharmaciesPlan = PlanDetails::where('work_plan_id', '=', $plan_id)->get();
            foreach ($PharmaciesPlan as $c) {
                $area = " ";
                $site_match = 0;
                $street_address = " ";
                $pharmacy = Pharmacy::where('id', '=', $c->Wpharmacy_id)->get()->first();
                $res=$this->get_location($c->Wlat, $c->Wlng);
                $c['pharmacy_name'] = $pharmacy->name;
                $c['area'] = $res["area"];
                $c['street_address'] = $res["street"];
                $c['site_match'] = $this->site_match($c->Wlat,$c->Wlng,$pharmacy->lat,$pharmacy->lan);
            }
        } catch (Exception $e) {
            return redirect('admin/sales-man/work-plans/list');
        }
        return view('admin-views.work-plan.details', compact('PharmaciesPlan', 'search'));
    }

    public function get_location($lat, $lng)
    {

        $res=array();
        try {
        $apikey = "AIzaSyCPsxZeXKcSYK1XXw0O0RbrZiI_Ekou5DY";
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lng&key=$apikey";
        $header = array(
            "authorization: key=" . $apikey . "",
            "content-type: application/json"
        );
        $ch = curl_init();
        $timeout = 120;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // Get URL content
        $result =curl_exec($ch);

        $result=json_decode($result);
        // close handle to release resources
        curl_close($ch);

       return $res=[
        'area' => $result->results[0]->address_components[2]->long_name,
        'street'=> $result->results[0]->address_components[1]->long_name
       ];
    } catch (Exception $e) {
        return $res=[
            'area' => " ",
            'street'=> " "
           ];
    }

    }


    public function site_match($latSite, $lngSite, $latPharmacy, $lngPharmacy)
    {
        $result = 0;
        $Start_distance = $this->distance($latSite, $lngSite, $latPharmacy, $lngPharmacy, "K");
        if ($Start_distance <= 0.1)
            $result = 1;
        else
            $result = 0;
        return $result;
    }
}
