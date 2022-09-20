<?php

namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Model\Area;
use App\Model\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionsController extends Controller
{
    public function get_cities()
    {

        try {
            $cities = City::get()->makeHidden(
                [
                    'updated_at', 'created_at', 'deleted_at'
                ]
            );
        } catch (\Exception $e) {
        }

        return response()->json($cities, 200);
    }


    public function get_areas(Request $request)
    {

        try {

            $city_areas = Area::join("cities", "cities.id", "=", "areas.city_id")
            ->where("areas.city_id",$request->city_id)
            ->get()->makeHidden(
                [
                    'updated_at', 'created_at', 'deleted_at','city_id','city_name','city_status'
                ]
            );

        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }

        return response()->json($city_areas, 200);
    }

}
