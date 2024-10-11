@foreach ($transcations as $key => $transcation)

<tr>
    <td>{{ Str::limit($transcation->id, 40, '...') }}</td>
    <td>
        {{ $transcation->created_at->format('d M Y') }}
    </td>
    <td>
        <a href="{{ route('admin.restaurant.view', $transcation->restaurant->id) }}"
            alt="view restaurant" class="table-rest-info">
            <h5>
                {{ Str::limit($transcation->restaurant->name, 20, '...') }}
            </h5>
        </a>
    </td>

    <td>{{ \App\CentralLogics\Helpers::format_currency($transcation->package->price) }}</td>
    <td>{{ $transcation->validity }} {{ translate('messages.Days') }}</td>

    <td>
        <div>
            @if ($transcation->payment_status == 'success')
            {{translate('paid')}}
            @else
            {{translate('Unpaid')}}
            @endif

                {{ \App\CentralLogics\Helpers::format_currency($transcation->paid_amount) }}
        </div>


        @if ($transcation->payment_status == 'success')
        <small class="text-success text-capitalize">
            {{  translate($transcation->payment_status) }}
        </small>
    @elseif($transcation->payment_status == 'on_hold')
    <small class="text-warning text-capitalize">
        {{ translate('messages.Payment_On_Hold') }}
    </small>

    @elseif($transcation->payment_status == 'failed')
    <small class="text-danger text-capitalize">
        {{ translate('messages.Payment_Failed') }}
    </small>

    @endif
    </td>

        <td>
        @if ($transcation->payment_method == 'wallet')
            {{ translate('messages.Wallet_payment') }}
        @elseif($transcation->payment_method == 'manual_payment_admin')
            {{ translate('messages.Manual_payment') }}
        @elseif($transcation->payment_method == 'manual_payment_by_restaurant')
            {{ translate('messages.Manual_payment') }}
        @elseif($transcation->payment_method == 'free_trial')
            {{ translate('messages.free_trial') }}
        @elseif($transcation->payment_method == 'pay_now')
            {{ translate('messages.Digital_Payment') }}
        @else
        {{ translate($transcation->payment_method) }}
        @endif
        </td>
    <td>
        @if ($transcation->payment_status == 'success')
        <div class="btn--container justify-content-center">
            <a class="btn btn-sm btn--warning btn-outline-warning action-btn"
                href="{{ route('admin.subscription.invoice', [$transcation['id']]) }}"
                title="{{ translate('messages.view_restaurant') }}"><i
                    class="tio-invisible"></i>
            </a>
        </div>
        @endif
    </td>
</tr>
@endforeach
