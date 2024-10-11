<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Zone;
use App\Models\Admin;
use App\Library\Payer;
use App\Models\Vendor;
use App\Traits\Payment;
use App\Library\Receiver;
use App\Models\Restaurant;
use App\Models\DataSetting;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Models\SubscriptionPackage;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Library\Payment as PaymentInfo;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use MatanYadaev\EloquentSpatial\Objects\Point;

class VendorController extends Controller
{
    public function create()
    {
        $status = BusinessSetting::where('key', 'toggle_restaurant_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            Toastr::error(translate('messages.not_found'));
            return back();
        }
        $page_data=   DataSetting::Where('type' , 'restaurant')->where('key' , 'restaurant_page_data')->first()?->value;
        $page_data =  $page_data ? json_decode($page_data ,true)  :[];
        return view('vendor-views.auth.register-step-1',compact('page_data')) ;
    }

    public function store(Request $request)
    {
        $status = BusinessSetting::where('key', 'toggle_restaurant_registration')->first();
        if(!isset($status) || $status->value == '0')
        {
            Toastr::error(translate('messages.not_found'));
            return back();
        }
        $validator = Validator::make($request->all(), [
            'f_name' => 'required',
            'name' => 'required|max:191',
            'address' => 'required',
            'latitude' => 'required|numeric|min:-90|max:90',
            'longitude' => 'required|numeric|min:-180|max:180',
            'email' => 'required|email|unique:vendors',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|unique:vendors',
            'minimum_delivery_time' => 'required',
            'maximum_delivery_time' => 'required',
            'zone_id' => 'required',
            'logo' => 'required|max:2048',
            'cover_photo' => 'required|max:2048',
            // 'additional_documents' => 'nullable|array|max:5',
            // 'additional_documents.*' => 'nullable|max:2048',
            'tax' => 'required',
            'delivery_time_type'=>'required',
            'password' => ['required', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],

        ],[
            'password.min_length' => translate('The password must be at least :min characters long'),
            'password.mixed' => translate('The password must contain both uppercase and lowercase letters'),
            'password.letters' => translate('The password must contain letters'),
            'password.numbers' => translate('The password must contain numbers'),
            'password.symbols' => translate('The password must contain symbols'),
            'password.uncompromised' => translate('The password is compromised. Please choose a different one'),
            'password.custom' => translate('The password cannot contain white spaces.'),
        ]);

        if($request->name[array_search('default', $request->lang)] == '' || $request->address[array_search('default', $request->lang)] == '' ){
            Toastr::error(translate('All_default_Restaurant_name_and_Address_is_required'));
            return back();
        }



        if($request?->latitude == null  &&  $request?->longitude == null){
            return back()->withErrors($validator)->withInput();
        }

        if($request->zone_id)
        {
            $zone = Zone::query()
            ->whereContains('coordinates', new Point($request->latitude, $request->longitude, POINT_SRID))
            ->where('id',$request->zone_id)
            ->first();
            if(!$zone){
                $validator->getMessageBag()->add('latitude', translate('messages.coordinates_out_of_zone'));
                return back()->withErrors($validator)->withInput();
            }
        }


        if ($request->delivery_time_type == 'min') {
            $minimum_delivery_time = (int) $request->input('minimum_delivery_time');
            if ($minimum_delivery_time < 10) {
                $validator->getMessageBag()->add('minimum_delivery_time', translate('messages.minimum_delivery_time_should_be_more_than_10_min'));
                return back()->withErrors($validator)->withInput();
            }
        }


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }
        try{
            $cuisine_ids = [];
            $cuisine_ids=$request->cuisine_ids;

            DB::beginTransaction();
            $vendor = new Vendor();
            $vendor->f_name = $request->f_name;
            $vendor->l_name = $request->l_name;
            $vendor->email = $request->email;
            $vendor->phone = $request->phone;
            $vendor->password = bcrypt($request->password);
            $vendor->status = null;
            $vendor->save();

            $restaurant = new Restaurant;
            $restaurant->name =  $request->name[array_search('default', $request->lang)];
            $restaurant->phone = $request->phone;
            $restaurant->email = $request->email;
            $restaurant->logo = Helpers::upload( dir:'restaurant/',  format:'png', image:  $request->file('logo'));
            $restaurant->cover_photo = Helpers::upload( dir:'restaurant/cover/', format: 'png',  image: $request->file('cover_photo'));
            $restaurant->address = $request->address[array_search('default', $request->lang)];
            $restaurant->latitude = $request->latitude;
            $restaurant->longitude = $request->longitude;
            $restaurant->vendor_id = $vendor->id;
            $restaurant->zone_id = $request->zone_id;
            $restaurant->tax = $request->tax;
            $restaurant->delivery_time =$request->minimum_delivery_time .'-'. $request->maximum_delivery_time.'-'.$request->delivery_time_type;
            $restaurant->status = 0;
            $restaurant->restaurant_model = 'none';

            if(isset($request->additional_data)  && count($request->additional_data) > 0){
                $restaurant->additional_data = json_encode($request->additional_data) ;
            }

            $additional_documents = [];
            if ($request->additional_documents) {
                foreach ($request->additional_documents as $key => $data) {
                    $additional = [];
                    foreach($data as $file){
                        if(is_file($file)){
                            $file_name = Helpers::upload('additional_documents/', $file->getClientOriginalExtension(), $file);
                            $additional[] = ['file'=>$file_name, 'storage'=> Helpers::getDisk()];
                        }
                        $additional_documents[$key] = $additional;
                    }
                }
                $restaurant->additional_documents = json_encode($additional_documents);
            }

            $restaurant->save();
            $restaurant?->cuisine()?->sync($cuisine_ids);
            $restaurant->tags()->sync($tag_ids);

        $default_lang = str_replace('_', '-', app()->getLocale());
        $data = [];
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->name[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Restaurant',
                        'translationable_id' => $restaurant->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $restaurant->name,
                    ));
                }
            }else{
                if ($request->name[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Restaurant',
                        'translationable_id' => $restaurant->id,
                        'locale' => $key,
                        'key' => 'name',
                        'value' => $request->name[$index],
                    ));
                }
            }
            if($default_lang == $key && !($request->address[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Restaurant',
                        'translationable_id' => $restaurant->id,
                        'locale' => $key,
                        'key' => 'address',
                        'value' => $restaurant->address,
                    ));
                }
            }else{
                if ($request->address[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Restaurant',
                        'translationable_id' => $restaurant->id,
                        'locale' => $key,
                        'key' => 'address',
                        'value' => $request->address[$index],
                    ));
                }
            }
        }
        Translation::insert($data);

            DB::commit();
            try{
                $admin= Admin::where('role_id', 1)->first();
                $notification_status= Helpers::getNotificationStatusData('restaurant','restaurant_registration');

                if( $notification_status?->mail_status == 'active' && config('mail.status') && Helpers::get_mail_status('registration_mail_status_restaurant') == '1'){
                    Mail::to($request['email'])->send(new \App\Mail\VendorSelfRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
                }
                $notification_status= null ;
                $notification_status= Helpers::getNotificationStatusData('admin','restaurant_self_registration');

                if( $notification_status?->mail_status == 'active' && config('mail.status') &&  Helpers::get_mail_status('restaurant_registration_mail_status_admin')== '1'){
                    Mail::to($admin['email'])->send(new \App\Mail\RestaurantRegistration('pending', $vendor->f_name.' '.$vendor->l_name));
                }
            }catch(\Exception $exception){
                info([$exception->getFile(),$exception->getLine(),$exception->getMessage()]);
            }
            $restaurant_id = $restaurant->id;
            $admin_commission= BusinessSetting::where('key','admin_commission')->first();
            $business_name= BusinessSetting::where('key','business_name')->first();
            $packages= SubscriptionPackage::where('status',1)->get();
            if (Helpers::subscription_check()) {
                Toastr::success(translate('messages.your_registration_info_is_saved_successfully_now_please_choose_your_business_model'));
                return view('vendor-views.auth.register-step-2',[
                    'restaurant_id' => $restaurant_id,
                    'packages' =>$packages,
                    'business_name' =>$business_name?->value,
                    'admin_commission' =>$admin_commission?->value,
                ]);
            } else{
                $restaurant->restaurant_model = 'commission';
                $restaurant->save();
                Toastr::success(translate('messages.your_restaurant_registration_is_successful'));
                return view('vendor-views.auth.register-step-4',[
                    'logo'=> $restaurant->logo_full_url
                ]);
            }

        }catch(\Exception $ex){
            DB::rollback();
            info($ex->getMessage());
            // dd($ex);
            Toastr::success(translate('messages.something_went_wrong_Please_try_again.'));
            return back();
        }

    }

    public function business_plan(Request $request){
            $restaurant=Restaurant::findOrFail($request->restaurant_id);
            if ($request->business_plan == 'subscription-base' && $request->package_id != null ) {
                return view('vendor-views.auth.register-step-3',[
                'package_id'=> $request->package_id,
                'restaurant_id' => $request->restaurant_id,
                'type'=>$request->type
                ]);
            }
            elseif($request->business_plan == 'commission-base' ){
                $restaurant->restaurant_model = 'commission';
                $restaurant->save();
                return view('vendor-views.auth.register-step-4',[
                    'logo'=> $restaurant->logo_full_url,
                    'type'=>$request->type
                ]);
            }
            else{
                $admin_commission= BusinessSetting::where('key','admin_commission')->first();
                $business_name= BusinessSetting::where('key','business_name')->first();
                $packages= SubscriptionPackage::where('status',1)->get();
                Toastr::error(translate('messages.please_follow_the_steps_properly.'));
                return view('vendor-views.auth.register-step-2',[
                    'admin_commission'=> $admin_commission?->value,
                    'business_name'=> $business_name?->value,
                    'packages'=> $packages,
                    'restaurant_id' => $request->restaurant_id,
                    'type'=>$request->type
                    ]);
            }
    }


    public function payment(Request $request){
        $restaurant_id=$request->restaurant_id;
        $package_id=$request->package_id;
        $payment_method=$request->payment_method ?? 'free_trial';
        $reference=$request->reference ?? null;
        $discount=$request->discount ?? 0;
        $restaurant=Restaurant::findOrFail($restaurant_id);
        $type=$request->type ?? 'new_join';
            if($request->payment == 'free_trial' ){
                    $status=  Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id ,package_id:$package_id, payment_method:$payment_method ,discount:$discount,reference:$reference ,type:$type);
                    if($status === 'downgrade_error'){
                        Toastr::error(translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits') );
                        return back();
                        }
                    Toastr::success(translate('messages.application_placed_successfully'));
                    return view('vendor-views.auth.register-step-4',[
                        'logo'=> $restaurant->logo_full_url
                    ]);
            }
            elseif($request->payment == 'paying_now'){
                    $payment_method= 'pay_now';
                    $status = Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id ,package_id:$package_id,payment_method: $payment_method ,discount:$discount, reference:$reference ,type:$type);
                    if($status === 'downgrade_error'){
                        Toastr::error(translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits') );
                        return back();
                        }
                        return to_route('vendor.subscription.digital_payment_methods' , ['subscription_transaction_id'=>$status , 'type' => $type]);

                // $payment_method='manual_payment_by_restaurant';
                // $status=  Helpers::subscription_plan_chosen(restaurant_id:$restaurant_id ,package_id:$package_id, payment_method:$payment_method ,discount:$discount,reference:$reference ,type:$type);
                // if($status === 'downgrade_error'){
                //     Toastr::error(translate('messages.You_can_not_downgraded_to_this_package_please_choose_a_package_with_higher_upload_limits') );
                //     return back();
                //     }
                // Toastr::success(translate('messages.application_placed_successfully'));
                // return view('vendor-views.auth.register-step-4',[
                //     'logo'=> $restaurant->logo_full_url
                // ]);
            }
    }

    public function back(Request $request){
        $restaurant_id = decrypt($request->restaurant_id);
        $admin_commission= BusinessSetting::where('key','admin_commission')->first();
        $business_name= BusinessSetting::where('key','business_name')->first();
        $packages= SubscriptionPackage::where('status',1)->get();
        return view('vendor-views.auth.register-step-2',[
            'admin_commission'=> $admin_commission?->value,
            'business_name'=> $business_name?->value,
            'packages'=> $packages,
            'restaurant_id' => $restaurant_id
            ]);
    }

}
