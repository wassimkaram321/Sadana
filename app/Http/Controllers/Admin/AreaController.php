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
    public function group_areas_list(Request $request, $id)
    {
        $group_areas = Area::join("group_area", "group_area.id", "=", "areas.group_id")
            ->where("areas.group_id", $id)
            ->get();
        $group_id=$id;
        return view('admin-views.area.list', compact('group_areas','group_id'));
    }

    public function area_store(Request $request, $group_id)
    {

        try {
            $area = new Area();
            $area->group_id = $group_id;
            $area->area_name = $request->area_name;
            $area->area_num = $request->area_num;
            $area->area_status = 1;
            $area->save();
            Toastr::success('Area added successfully!');
            return back();
        } catch (Exception $e) {
            return back();
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
