@foreach($packages as $key=>$package)
<tr class="">

    <td class="">
        {{$key+$packages->firstItem()}}
    </td>
    <td class="">
        <a href="{{route('admin.subscription.package_details',[$package['id']])}}" class="text--title text-hover">
            {{ Str::limit($package->package_name, 20, '...')   }}
        </a>
    </td>
    <td>
        <div>
            {{ \App\CentralLogics\Helpers::format_currency($package->price) }}
        </div>
    </td>
    <td>
        <div>
            {{$package->validity}}
        </div>
    </td>
    <td>
        <div class="pl-4 ml-2">
            {{$package->transactions_count}}
        </div>
    </td>
    <td>
        <div class="d-flex justify-content-center">
            <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$package->id}}">
                <input type="checkbox" data-url="{{route('admin.subscription.package_status',[$package->id,$package->status?0:1])}}" data-message="{{$package->status?translate('Do_You_Want_To_Disable_This_Package'):translate('Do_you_want_to_Active_This_Package')}}"
                class="toggle-switch-input status_change_alert" id="stocksCheckbox{{$package->id}}" {{$package->status?'checked':''}}>
                <span class="toggle-switch-label">
                    <span class="toggle-switch-indicator"></span>
                </span>
            </label>
        </div>
    </td>


    <td>
        <div class="btn--container justify-content-center">
            <a class="btn btn--primary btn-outline-primary action-btn" href="{{ route('admin.subscription.package_edit',$package->id) }}" title="{{translate('messages.edit_Package')}}"><i class="tio-edit"></i>
            </a>
            <a class="btn btn-sm btn--warning btn-outline-warning action-btn"
                href="{{route('admin.subscription.package_details',[$package['id']])}}" title="{{translate('messages.view_Package')}}"><i class="tio-visible-outlined"></i>
            </a>
        </div>
    </td>



</tr>
@endforeach
