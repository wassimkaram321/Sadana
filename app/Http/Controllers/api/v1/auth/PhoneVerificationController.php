<?php

namespace App\Http\Controllers\api\v1\auth;

use App\CPU\Helpers;
use App\CPU\SMS_module;
use App\User;
use App\Http\Controllers\Controller;
use App\Model\PhoneOrEmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use function App\CPU\translate;
use App\Http\Traits\GeneralTrait;

class PhoneVerificationController extends Controller
{
    use GeneralTrait;
    public function check_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //'temporary_token' => 'required',
            'phone' => 'required|min:9|max:9|unique:users,phone'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>404 ,'errors' => Helpers::error_processor($validator)], 404);
        }

        // $user = User::where(['temporary_token' => $request->temporary_token])->first();

        // if (isset($user) == false) {
        //     return response()->json([
        //         'message' => translate('temporary_token_mismatch'),
        //     ], 200);
        // }
        $code = rand(100000, 999999);
        $phone = PhoneOrEmailVerification::where(['phone_or_email' => $request['phone']])->first();
        if(isset($phone) != false)
        {
            $phone->token=$code;
            $phone->save();
            $details=[
                'phone_or_email' => $request['phone'],
                'token' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            return response()->json(['status'=>200,'message' =>$details], 200);
        }
        else
        {
            $phone=PhoneOrEmailVerification::updateOrCreate([
                'phone_or_email' => $request['phone'],
                'token' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $details=[
                'phone_or_email' => $request['phone'],
                'token' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            return response()->json(['status'=>200 ,'message' => $details], 200);
            //return $this->returnData('Details', $details, ' Phone and code verifications');
        }

        //  $response = SMS_module::send($request['phone'], $token);
        // return response()->json([
        //     'message' => $response,
        //     'token' => 'active'
        // ], 200);

    }

    public function verify_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:10|numeric',
            //'temporary_token' => 'required',
            'otp' => 'required|min:6|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>404 ,'errors' => Helpers::error_processor($validator)], 404);
            //return $this->returnError(Helpers::error_processor($validator),403);
        }

        $verify = PhoneOrEmailVerification::where(['phone_or_email' => $request['phone'], 'token' => $request['otp']])->first();

        if (isset($verify)) {
            try {
                // $user = User::where(['temporary_token' => $request['temporary_token']])->first();
                // $user->phone = $request['phone'];
                // $user->is_phone_verified = 1;
                // $user->save();
                $verify->delete();
                return response()->json(['status'=>200 ,'message' => translate('otp_verified')], 200);
            } catch (\Exception $exception) {
                return response()->json(['status'=>404 ,'errors' => translate('otp_not_verified')], 404);
                //return $this->returnError(Helpers::error_processor($validator),200);
            }

            // $token = $user->createToken('LaravelAuthApp')->accessToken;
            // return response()->json([
            //     'message' => translate('otp_verified'),
            //     'token' => $token
            // ], 200);
        }
        else
        {
            return response()->json(['status'=>404 ,'errors' => translate('otp_not_found')], 404);
        }
        // return response()->json(['errors' => [
        //     ['code' => 'token', 'message' => translate('otp_not_found')]
        // ]], 404);
    }
}
