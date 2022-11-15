<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Bag;
use App\Model\Area;
use App\Model\PlanDetails;
use App\Model\BagsSetting;
use App\Model\BagProduct;
use App\Pharmacy;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use function App\CPU\translate;
class BagController extends Controller
{
    public function get_bags(Request $request)
    {
        try {
            $details = array();
            $user = $request->user();
            if ($user->user_type == "salesman") {
                $validator = Validator::make($request->all(), [
                    'pharmacy_id' => 'required'
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => Helpers::error_processor($validator)], 403);
                }
                $pharmacy = Pharmacy::where('id', '=', $request->pharmacy_id)->get()->first();
                $userPh = User::where('id', '=', $pharmacy->user_id)->get()->first();

                $area_id = $userPh->area_id;
                $group = Area::where('id', '=', $area_id)->get()->first();
                $user_group_id = $group->group_id;
                $vip = $userPh->pharmacy->vip;
                $pharmacyId = $pharmacy->id;
            } else {
                $area_id = $user->area_id;
                $group = Area::where('id', '=', $area_id)->get()->first();
                $user_group_id = $group->group_id;
                $vip = $user->pharmacy->vip;
                $pharmacyId = $user->pharmacy->id;
            }

            $today = Carbon::now();
            $bags = Bag::whereDate('end_date', '>=', $today->format('Y-m-d'))
                ->where('bag_status', '=', 1)
                ->get()->makeHidden(
                    [
                        'updated_at', 'created_at', 'deleted_at'
                    ]
                );

            foreach ($bags as $bag) {
                $bagSetting = BagsSetting::where('bag_id', '=', $bag->id)->get()->first();
                if ($bagSetting->all == 1)
                    array_push($details, $bag);
                else {
                    if ($bagSetting->vip == 1 && $vip == 1) {
                        array_push($details, $bag);
                    } elseif ($bagSetting->vip == 0 && $vip == 0 || $bagSetting->vip == 0 && $vip == 1 || $bagSetting->vip == 1 && $vip == 0) {
                        if ($bagSetting->custom == 0 && $bagSetting->vip == 0 && $vip == 0 && $bagSetting->custom_pharmacy == 0) {
                            array_push($details, $bag);
                        }
                        if ($bagSetting->custom == 1) {
                            $group_ids = json_decode($bagSetting->group_ids);
                            for ($i = 0; $i < count($group_ids); $i++) {
                                if ($group_ids[$i] == $user_group_id) {
                                    array_push($details, $bag);
                                }
                            }
                        }
                        if ($bagSetting->custom_pharmacy == 1) {
                            $pharmacy_ids = json_decode($bagSetting->pharmacy_ids);
                            for ($i = 0; $i < count($pharmacy_ids); $i++) {
                                if ($pharmacy_ids[$i] == $pharmacyId) {
                                    array_push($details, $bag);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
        }

        return response()->json($details, 200);
    }

    public function get_bag_products(Request $request)
    {
        try {
            $bag_products = BagProduct::join("products", "products.id", "=", "products_bag.product_id")
                ->where("products_bag.bag_id", $request->bag_id)
                ->get([
                    'products.name', 'products.thumbnail',
                    'products_bag.product_count',
                    'products_bag.product_price', 'products_bag.product_total_price',
                ]);
        } catch (\Exception $e) {
        }

        return response()->json($bag_products, 200);
    }


  
}
