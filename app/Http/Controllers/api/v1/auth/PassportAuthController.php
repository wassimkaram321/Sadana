<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\City;
use App\Model\Group;
use App\Model\Area;
use App\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Traits\GeneralTrait;
use function App\CPU\translate;

class PassportAuthController extends Controller
{
    use GeneralTrait;
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required',
            'pharmacy_name' => 'required',
            'land_number' => 'required',
            'from' => 'required',
            'to' => 'required',
            'statusToday' => 'required',
            'Address' => 'required',
            'region_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 404);
        }


        $area = Area::where('id',$request->region_id)->get()->first();
        $group = Group::where('id',$area->group_id)->get()->first();
        $city=City::where('id',$group->city_id)->get()->first();

        //$temporary_token = Str::random(40);

        //return response()->json(['token' => $area], 200);
        $user = User::create([
            'name'=> $request->f_name.' '.$request->l_name,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'city' => $city->city_name,
            'country'=>$group->group_name,
            'is_active' => 0,
            'is_phone_verified'=>1,
            'password' => bcrypt($request->password),
            'area_id' => $area->id,
            //'temporary_token' => $temporary_token,
        ]);
        $user->user_type="pharmacist";
        $user->save();
        $pharmacy=Pharmacy::create([
            'name' => $request->pharmacy_name,
            'land_number' => $request->land_number,
            'from' => $request->from,
            'to' => $request->to,
            'statusToday' => $request->statusToday,
            'Address' => $request->Address,
            'city' => $city->city_name,
            'user_type_id' => "pharmacist",
            'lat' => $request->lat,
            'lan' => $request->lng,
            'region'=> $area->area_name,
            'user_id'=>$user->id
        ]);

        $user->pharmacy()->save($pharmacy);


        // $phone_verification = Helpers::get_business_settings('phone_verification');
        // $email_verification = Helpers::get_business_settings('email_verification');
        // if ($phone_verification && !$user->is_phone_verified) {
        //     return response()->json(['temporary_token' => $temporary_token], 200);
        // }
        // if ($email_verification && !$user->is_email_verified) {
        //     return response()->json(['temporary_token' => $temporary_token], 200);
        // }

        $token = $user->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token], 200);
    }



    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'email' => 'required',
            'phone' => 'required|numeric',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 404);
        }

        $user_id = $request['phone'];
        if (filter_var($user_id, FILTER_VALIDATE_EMAIL)) {
            $medium = 'email';
        } else {
           // $count = strlen(preg_replace("/[^\d]/", "", $user_id));
           $count = strlen($user_id);
            if ($count >= 9 && $count <= 15) {
                $medium = 'phone';
            } else {
                $errors = [];
                array_push($errors, ['code' => 'email', 'message' => 'Invalid phone number']);
                return response()->json([
                    'errors' => $errors
                ], 404);
            }
        }

        $user = User::where([$medium => $user_id])->first();

        $data = [
            $medium => $user_id,
            'password' => $request->password
        ];


        if (isset($user) && $user->is_active && auth()->attempt($data)) {
            $user->temporary_token = Str::random(40);
            $user->save();

            $phone_verification = Helpers::get_business_settings('phone_verification');
            //$email_verification = Helpers::get_business_settings('email_verification');
            if ($phone_verification && !$user->is_phone_verified) {
                return response()->json(['temporary_token' => $user->temporary_token], 200);
            }
            // if ($email_verification && !$user->is_email_verified) {
            //     return response()->json(['temporary_token' => $user->temporary_token], 200);
            // }
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('Customer_not_found_or_Account_has_been_suspended_or_wrong_password')]);
            return response()->json([
                'errors' => $errors
            ], 404);
        }
    }
}
