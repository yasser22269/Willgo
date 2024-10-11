<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Validation\Rules\Password;

class SystemController extends Controller
{

    public function restaurant_data()
    {
        $new_order = DB::table('orders')->where(['checked' => 0])->count();
        return response()->json([
            'success' => 1,
            'data' => ['new_order' => $new_order]
        ]);
    }

    public function settings()
    {
        return view('admin-views.settings');
    }

    public function settings_update(Request $request)
    {
        $admin = Admin::findOrFail(auth('admin')?->id());
        $request->validate([
            'f_name' => 'required',
            'l_name' => 'required',
            'email' => 'required|unique:admins,email,'.$admin->id,
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:9|unique:admins,phone,'.$admin->id,
        ], [
            'f_name.required' => translate('messages.first_name_is_required'),
            'l_name.required' => translate('messages.Last name is required!'),
        ]);

        if ($request->has('image')) {
            $image_name =Helpers::update(dir:'admin/', old_image: $admin->image, format: 'png', image: $request->file('image'));
        } else {
            $image_name = $admin['image'];
        }

        $admin->f_name = $request->f_name;
        $admin->l_name = $request->l_name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->image = $image_name;
        $admin->save();
        Toastr::success(translate('messages.admin_updated_successfully'));
        return back();
    }

    public function settings_password_update(Request $request)
    {
        $request->validate([
            'password' => ['required','same:confirm_password', Password::min(8)->mixedCase()->letters()->numbers()->symbols()->uncompromised()],
            'confirm_password' => 'required',
        ],[
            'password.min_length' => translate('The password must be at least :min characters long'),
            'password.mixed' => translate('The password must contain both uppercase and lowercase letters'),
            'password.letters' => translate('The password must contain letters'),
            'password.numbers' => translate('The password must contain numbers'),
            'password.symbols' => translate('The password must contain symbols'),
            'password.uncompromised' => translate('The password is compromised. Please choose a different one'),
            'password.custom' => translate('The password cannot contain white spaces.'),
        ]);

        $admin = Admin::findOrFail(auth('admin')->id());
        $admin->password = bcrypt($request['password']);
        $admin->save();
        Toastr::success(translate('messages.admin_password_updated_successfully'));
        return back();
    }

    public function maintenance_mode()
    {

        if(env('APP_MODE') == 'demo'){
            Toastr::warning('Sorry! You can not enable maintainance mode in demo!');
            return back();
        }
        $maintenance_mode = BusinessSetting::where('key', 'maintenance_mode')->first();
        if (isset($maintenance_mode) == false) {
            BusinessSetting::insert([
                'key' => 'maintenance_mode',
                'value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            BusinessSetting::where(['key' => 'maintenance_mode'])->update([
                'key' => 'maintenance_mode',
                'value' => $maintenance_mode->value == 1 ? 0 : 1,
                'updated_at' => now(),
            ]);
        }

        if ( $maintenance_mode?->value){
            Toastr::success(translate('messages.Maintenance_is_off'));
            return back();
        }
        Toastr::success(translate('messages.Maintenance_is_on'));
    return back();
    }

    public function update_fcm_token(Request $request){
        $admin = $request?->user();
        $admin->firebase_token = $request->token;
        $admin?->save();

        return response()->json([]);

    }
    public function landing_page()
    {
        $landing_page = BusinessSetting::where('key', 'landing_page')->first();
        BusinessSetting::updateOrCreate(['key' => 'landing_page'], [
                'value' =>$landing_page?->value == 1 ? 0 : 1,
            ]);

        if (isset($landing_page) && $landing_page->value) {
            return response()->json(['message' => translate('landing_page_is_off.')]);
        }
        return response()->json(['message' => translate('landing_page_is_on.')]);
    }
    public function system_currency(Request $request)
    {
        $currency_check=Helpers::checkCurrency($request['currency']);
        if( $currency_check !== true ){
        return response()->json(['data'=> translate($currency_check) ],200);
        }
        return response()->json([],200);
    }
}
