<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Models\Guest;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Carbon;
use App\Mail\EmailVerification;
use App\Mail\LoginVerification;
use App\Models\BusinessSetting;
use App\CentralLogics\SMS_module;
use App\Models\PhoneVerification;
use App\Models\WalletTransaction;
use App\Models\EmailVerifications;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Support\Facades\Mail;
use Modules\Gateways\Traits\SmsGateway;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function verify_phone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|min:9|max:14',
            'otp'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $user = User::where('phone', $request->phone)->first();
        if($user)
        {
            if($user->is_phone_verified)
            {
                return response()->json([
                    'message' => translate('messages.phone_number_is_already_varified')
                ], 200);

            }

            if(env('APP_MODE')=='demo')
            {
                if($request['otp']=="1234")
                {
                    $user->is_phone_verified = 1;
                    $user->save();

                    return response()->json([
                        'message' => translate('messages.phone_number_varified_successfully'),
                        'otp' => 'inactive'
                    ], 200);
                }
                return response()->json([
                    'message' => translate('messages.phone_number_and_otp_not_matched')
                ], 404);
            }

            $data = DB::table('phone_verifications')->where([
                'phone' => $request['phone'],
                'token' => $request['otp'],
            ])->first();

            if($data)
            {
                DB::table('phone_verifications')->where([
                    'phone' => $request['phone'],
                    'token' => $request['otp'],
                ])->delete();

                $user->is_phone_verified = 1;
                $user->save();
                return response()->json([
                    'message' => translate('messages.phone_number_varified_successfully'),
                    'otp' => 'inactive'
                ], 200);
            }
            else{
                // $otp_hit = BusinessSetting::where('key', 'max_otp_hit')->first();
                // $max_otp_hit =isset($otp_hit) ? $otp_hit->value : 5 ;
                $max_otp_hit = 5;

                // $otp_hit_time = BusinessSetting::where('key', 'max_otp_hit_time')->first();
                // $max_otp_hit_time =isset($otp_hit_time) ? $otp_hit_time->value : 30 ;

                $max_otp_hit_time = 60; // seconds
                $temp_block_time = 600; // seconds

                $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

                if(isset($verification_data)){


                    // if($verification_data->is_blocked == 1){
                    //     $errors = [];
                    //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.your_account_is_blocked')]);
                    //     return response()->json(['errors' => $errors ], 403);
                    // }



                    if(isset($verification_data->temp_block_time ) && Carbon::parse($verification_data->temp_block_time)->DiffInSeconds() <= $temp_block_time){
                        $time= $temp_block_time - Carbon::parse($verification_data->temp_block_time)->DiffInSeconds();

                        $errors = [];
                        array_push($errors, ['code' => 'otp_block_time',
                        'message' => translate('messages.please_try_again_after_').CarbonInterval::seconds($time)->cascade()->forHumans()
                         ]);
                        return response()->json([
                            'errors' => $errors
                        ], 405);
                    }

                    if($verification_data->is_temp_blocked == 1 && Carbon::parse($verification_data->updated_at)->DiffInSeconds() >= $max_otp_hit_time){
                        DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                            [
                                'otp_hit_count' => 0,
                                'is_temp_blocked' => 0,
                                'temp_block_time' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                    // if($verification_data->is_temp_blocked == 1 && Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time){
                    //         $errors = [];
                    //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds') ]);
                    //     return response()->json([
                    //         'errors' => $errors
                    //     ], 405);
                    //     }

                    if($verification_data->otp_hit_count >= $max_otp_hit &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time &&  $verification_data->is_temp_blocked == 0){

                        DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                            [
                            'is_temp_blocked' => 1,
                            'temp_block_time' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                            ]);
                        $errors = [];
                        array_push($errors, ['code' => 'otp_temp_blocked', 'message' => translate('messages.Too_many_attemps') ]);
                        return response()->json([
                            'errors' => $errors
                        ], 405);
                    }


                    // if($verification_data->otp_hit_count >= $max_otp_hit &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $max_otp_hit_time){

                    //     DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                    //         [
                    //         // 'is_temp_blocked' => 1,
                    //         'created_at' => now(),
                    //         'updated_at' => now(),
                    //         ]);
                    //         // $errors = [];
                    //         array_push($errors, ['code' => 'otp_warning', 'message' =>translate('messages.Too_many_attemps') ]);
                    //         return response()->json([
                    //             'errors' => $errors
                    //         ], 405);
                    // }
                }


                DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                [
                'otp_hit_count' => DB::raw('otp_hit_count + 1'),
                'updated_at' => now(),
                'temp_block_time' => null,
                ]);

                return response()->json([
                    'message' => translate('messages.phone_number_and_otp_not_matched')
                ], 404);
            }
        }
        return response()->json([
            'message' => translate('messages.not_found')
        ], 404);

    }

    public function check_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $email_verification= BusinessSetting::where(['key'=>'email_verification'])->first();

        if (isset($email_verification) && $email_verification->value){
            $token = rand(1000, 9999);
            DB::table('email_verifications')->insert([
                'email' => $request['email'],
                'token' => $token,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            try{
                $notification_status= Helpers::getNotificationStatusData('customer','customer_registration_otp');

            if($notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('registration_otp_mail_status_user') == '1') {
                $user = User::where('email', $request['email'])->first();
                Mail::to($request['email'])->send(new EmailVerification($token,$user->f_name));
            }
            }catch(\Exception $ex){
                info($ex->getMessage());
            }


            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'active'
            ], 200);
        }else{
            return response()->json([
                'message' => translate('Email is ready to register'),
                'token' => 'inactive'
            ], 200);
        }
    }

    public function verify_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $verify = EmailVerifications::where(['email' => $request['email'], 'token' => $request['token']])->first();

        if (isset($verify)) {
            $verify->delete();
            return response()->json([
                'message' => translate('messages.token_varified'),
            ], 200);
        }

        $errors = [];
        array_push($errors, ['code' => 'token', 'message' => translate('messages.token_not_found')]);
        return response()->json(['errors' => $errors ]
        , 404);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => ['required', Password::min(8)],

        ], [
            'f_name.required' => translate('The first name field is required.'),
            'l_name.required' => translate('The last name field is required.'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $ref_by= null ;
        $customer_verification = BusinessSetting::where('key','customer_verification')->first()->value;
        //Save point to refeer
        if($request->ref_code) {
            $ref_status = BusinessSetting::where('key','ref_earning_status')->first()->value;
            if ($ref_status != '1') {
                return response()->json(['errors'=>Helpers::error_formater('ref_code', translate('messages.referer_disable'))], 403);
            }

            $referar_user = User::where('ref_code', '=', $request->ref_code)->first();
            if (!$referar_user || !$referar_user->status) {
                return response()->json(['errors'=>Helpers::error_formater('ref_code',translate('messages.referer_code_not_found'))], 405);
            }

            if(WalletTransaction::where('reference', $request->phone)->first()) {
                return response()->json(['errors'=>Helpers::error_formater('phone',translate('Referrer code already used'))], 203);
            }

            $notification_data = [
                'title' => translate('Your_Referral_Code_Has_Been_Used!'),
                'description' => translate('Congratulations!_Your_referral_code_was_used_by_a_new_user._Get_ready_to_earn_rewards_when_they_complete_their_first_order.'),
                'order_id' => '',
                'image' => '',
                'type' => 'referral_code',
            ];
            $customer_push_notification_status=Helpers::getNotificationStatusData('customer','customer_new_referral_join');
            if( $customer_push_notification_status?->push_notification_status  == 'active' && $referar_user?->cm_firebase_token){
                Helpers::send_push_notif_to_device($referar_user?->cm_firebase_token, $notification_data);
                DB::table('user_notifications')->insert([
                    'data' => json_encode($notification_data),
                    'user_id' => $referar_user?->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }


            $ref_by= $referar_user->id;
        }

        $user = User::create([
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'ref_by' =>   $ref_by,
            'password' => bcrypt($request->password),
        ]);
        $user->ref_code = Helpers::generate_referer_code($user);
        $user->save();

        $token = $user->createToken('RestaurantCustomerAuth')->accessToken;

        if($customer_verification && env('APP_MODE') !='demo')
        {

            // $interval_time = BusinessSetting::where('key', 'otp_interval_time')->first();
            // $otp_interval_time= isset($interval_time) ? $interval_time->value : 20;
            $otp_interval_time= 60; //seconds
            $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

            if(isset($verification_data) &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $otp_interval_time){
                $time= $otp_interval_time - Carbon::parse($verification_data->updated_at)->DiffInSeconds();
                $errors = [];
                array_push($errors, ['code' => 'otp', 'message' =>  translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds')]);
                return response()->json([
                    'errors' => $errors
                ], 405);
            }

            $otp = rand(1000, 9999);
            DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                [
                'token' => $otp,
                'otp_hit_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                ]);

                try {
                    $mailResponse= null;
                    $mail_status = Helpers::get_mail_status('registration_otp_mail_status_user');
                    $notification_status= Helpers::getNotificationStatusData('customer','customer_registration_otp');

                    if($notification_status?->mail_status == 'active' && config('mail.status') && $mail_status == '1') {
                        Mail::to($request['email'])->send(new EmailVerification($otp,$request->f_name));
                        $mailResponse='success';
                    }
                }catch(\Exception $ex){
                    info($ex->getMessage());
                    $mailResponse=null;
                }

                $response =null;
                $customer_sms_status=Helpers::getNotificationStatusData('customer','customer_registration_otp');
                if($customer_sms_status?->sms_status  == 'active'){


                    $published_status = 0;
                    $payment_published_status = config('get_payment_publish_status');
                    if (isset($payment_published_status[0]['is_published'])) {
                        $published_status = $payment_published_status[0]['is_published'];
                    }

                    if($published_status == 1){
                        $response = SmsGateway::send($request['phone'],$otp);
                    }else{
                        $response = SMS_module::send($request['phone'],$otp);
                    }

                }


                if($response !== 'success' && $mailResponse !== 'success')
                {
                    $errors = [];
                    array_push($errors, ['code' => 'otp', 'message' => translate('messages.failed_to_send_sms_&_mail')]);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }

            // if($response != 'success')
            // {
            //     $errors = [];
            //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.faield_to_send_sms')]);
            //     return response()->json([
            //         'errors' => $errors
            //     ], 405);
            // }
        }
        try
        {
            $notification_status= Helpers::getNotificationStatusData('customer','customer_registration');

            if($notification_status?->mail_status == 'active' && config('mail.status') && $request->email && Helpers::get_mail_status('registration_mail_status_user') == '1') {
                Mail::to($request->email)->send(new \App\Mail\CustomerRegistration($request->f_name . ' ' . $request->l_name));
            }
        }
        catch(\Exception $ex)
        {
            info($ex->getMessage());
        }

        if($request->guest_id  && isset($user->id)){

            $userStoreIds = Cart::where('user_id', $request->guest_id)
                ->join('food', 'carts.item_id', '=', 'food.id')
                ->pluck('food.restaurant_id')
                ->toArray();

            Cart::where('user_id', $user->id)
                ->whereHas('item', function ($query) use ($userStoreIds) {
                    $query->whereNotIn('restaurant_id', $userStoreIds);
                })
                ->delete();

            Cart::where('user_id', $request->guest_id)->update(['user_id' => $user->id,'is_guest' => 0]);
        }

        return response()->json(['token' => $token,'is_phone_verified' => 0, 'phone_verify_end_url'=>"api/v1/auth/verify-phone" ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $data = [
            'phone' => $request->phone,
            'password' => $request->password
        ];

        $customer_verification = BusinessSetting::where('key','customer_verification')->first()->value;
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('RestaurantCustomerAuth')->accessToken;
            if(!auth()->user()->status)
            {
                $errors = [];
                array_push($errors, ['code' => 'auth-003', 'message' => translate('messages.your_account_is_blocked')]);
                return response()->json([
                    'errors' => $errors
                ], 403);
            }
            $user = auth()->user();
            if($customer_verification && !auth()->user()->is_phone_verified && env('APP_MODE') != 'demo')
            {

                // $interval_time = BusinessSetting::where('key', 'otp_interval_time')->first();
                // $otp_interval_time= isset($interval_time) ? $interval_time->value : 60;
                $otp_interval_time= 60; //seconds

                $verification_data= DB::table('phone_verifications')->where('phone', $request['phone'])->first();

                if(isset($verification_data) &&  Carbon::parse($verification_data->updated_at)->DiffInSeconds() < $otp_interval_time){

                    $time= $otp_interval_time - Carbon::parse($verification_data->updated_at)->DiffInSeconds();
                    $errors = [];
                    array_push($errors, ['code' => 'otp', 'message' =>  translate('messages.please_try_again_after_').$time.' '.translate('messages.seconds')]);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }

                $otp = rand(1000, 9999);
                DB::table('phone_verifications')->updateOrInsert(['phone' => $request['phone']],
                    [
                    'token' => $otp,
                    'otp_hit_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                    ]);

                    try {
                        $mailResponse=null;
                        $notification_status= Helpers::getNotificationStatusData('customer','customer_login_otp');
                        if ($notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('login_otp_mail_status_user') == '1') {
                            Mail::to($user['email'])->send(new LoginVerification($otp,$user->f_name));
                            $mailResponse='success';
                        }

                    }catch(\Exception $ex){
                        $mailResponse=null;
                        info($ex->getMessage());
                    }

                    $response= null;
                    $customer_sms_status=Helpers::getNotificationStatusData('customer','customer_login_otp');

                    if($customer_sms_status?->sms_status  == 'active'){

                        $published_status = 0;
                        $payment_published_status = config('get_payment_publish_status');
                        if (isset($payment_published_status[0]['is_published'])) {
                            $published_status = $payment_published_status[0]['is_published'];
                        }

                        if($published_status == 1){
                            $response = SmsGateway::send($request['phone'],$otp);
                        }else{
                            $response = SMS_module::send($request['phone'],$otp);
                        }
                    }




                if($response !== 'success' && $mailResponse !== 'success')
                {
                    $errors = [];
                    array_push($errors, ['code' => 'otp', 'message' => translate('messages.failed_to_send_sms_&_mail')]);
                    return response()->json([
                        'errors' => $errors
                    ], 405);
                }
                // if($response != 'success')
                // {
                //     $errors = [];
                //     array_push($errors, ['code' => 'otp', 'message' => translate('messages.faield_to_send_sms')]);
                //     return response()->json([
                //         'errors' => $errors
                //     ], 405);
                // }
            }

            if($user->ref_code == null && isset($user->id)){
                $ref_code = Helpers::generate_referer_code($user);
                DB::table('users')->where('phone', $user->phone)->update(['ref_code' => $ref_code]);
            }
            if($request->guest_id  && isset($user->id)){

                $userStoreIds = Cart::where('user_id', $request->guest_id)
                    ->join('food', 'carts.item_id', '=', 'food.id')
                    ->pluck('food.restaurant_id')
                    ->toArray();

                Cart::where('user_id', $user->id)
                    ->whereHas('item', function ($query) use ($userStoreIds) {
                        $query->whereNotIn('restaurant_id', $userStoreIds);
                    })
                    ->delete();

                Cart::where('user_id', $request->guest_id)->update(['user_id' => $user->id,'is_guest' => 0]);
            }
            return response()->json(['token' => $token, 'is_phone_verified'=>auth()->user()->is_phone_verified], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => translate('messages.Unauthorized')]);
            return response()->json([
                'errors' => $errors
            ], 401);
        }
    }

    public function guest_request(Request $request)
    {
        $guest = new Guest();
        $guest->ip_address = $request->ip();
        $guest->fcm_token = $request->fcm_token;

        if ($guest->save()) {
            return response()->json([
                'message' => translate('messages.guest_varified'),
                'guest_id' => $guest->id,
            ], 200);
        }

        return response()->json([
            'message' => translate('messages.failed')
        ], 404);
    }
}
