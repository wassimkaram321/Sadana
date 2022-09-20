<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Area;
use Illuminate\Http\Request;
use App\CPU\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class AreaController extends Controller
{
    public function city_areas_list(Request $request, $id)
    {
        $city_areas = Area::join("cities", "cities.id", "=", "areas.city_id")
            ->where("areas.city_id", $id)
            ->get();
        $city_id=$id;
        return view('admin-views.area.list', compact('city_areas','city_id'));
    }

    public function area_store(Request $request, $city_id)
    {
        try {
            $area = new Area();
            $area->city_id = $city_id;
            $area->area_name = $request->area_name;
            $area->area_num = $request->area_num;
            $area->area_status = $request->area_num;
            $area->save();
            Toastr::success('Area added successfully!');
            return back();
        } catch (Exception $e) {
            Toastr::success('Area added Failure!');
        }
    }

    public function area_delete(Request $request)
    {
        $area = Area::findOrFail($request->id);
        $area->delete();
        Toastr::success('Area deleted successfully!');
    }

    
}
