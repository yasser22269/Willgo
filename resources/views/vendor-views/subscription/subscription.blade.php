@extends('layouts.vendor.app')

@section('title', translate('messages.my_subscription'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset('/public/assets/landing/owl/dist/assets/owl.carousel.css')}}">
@endpush
@section('content')


<?php
        $digital_payment = App\CentralLogics\Helpers::get_business_settings('digital_payment');
        $digital_payment  = $digital_payment['status'];
?>
    <div class="content container-fluid">

        @if (isset($rest_subscription) &&  $rest_subscription->status == 1 && $rest_subscription->expiry_date <= Carbon\Carbon::today()->addDays('7'))
        <div class="__alert alert alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="__warning-icon">
                    <i class="tio-warning-outlined"></i>
                </div>
                <div class="w-0 flex-grow pl-3">
                    <h6> {{  translate('messages.attention') }}</h6>
                    <div>
                        {{  translate('messages.Your_Subcription_is_Ending_Soon._Please_Renew_Before') }}
                        {{ $rest_subscription->expiry_date->format('d M Y') }} {{  translate('messages.Otherwise_All_Your_Activities_will_Turn_Off_Automatically_After_That.') }}
                    </div>
                </div>
            </div>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <i class="tio-clear"></i>
            </button>
        </div>
        @endif
        @if (isset($rest_subscription) &&  $rest_subscription?->status == 0 && $restaurant?->restaurant_sub_trans?->transaction_status == 0 )
        <div style="background: rgba(255, 173, 50, 0.938)"  class="__alert alert alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="__warning-icon">
                    <i class="tio-danger-outlined"></i>
                </div>
                <div class="w-0 flex-grow pl-3">
                    <h6> {{  translate('messages.attention') }}</h6>
                    <div>
                        {{  translate('messages.Your_Subcription_is_on_hold._your_current_payment_status') }} {{ translate($restaurant?->restaurant_sub_trans?->payment_status) }}
                    </div>
                </div>
            </div>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <i class="tio-clear"></i>
            </button>
        </div>
        @endif
        @if (isset($rest_subscription) && $rest_subscription->status == 0 && $restaurant?->restaurant_sub_trans?->payment_status == 'success' )
        <div style="background: red"  class="__alert alert alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div class="__warning-icon">
                    <i class="tio-danger-outlined"></i>
                </div>
                <div class="w-0 flex-grow pl-3">
                    <h6> {{  translate('messages.attention') }}</h6>
                    <div>
                        {{  translate('messages.Your_Subcription_has_expired_on') }}
                        {{ $rest_subscription->expiry_date->format('d M Y') }}.
                        {{ translate('messages.All_Your_Activities_has_been_Turn_Off_Automatically_To_Continue_Your_Activities_Please_Select_a_Package.') }}
                    </div>
                </div>
            </div>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <i class="tio-clear"></i>
            </button>
        </div>
        @endif


        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                    <i class="tio-museum"></i> <span>{{ $restaurant->name }}'s
                        {{ translate('messages.subscription') }}</span>
                </h1>
                <div class="btn--container justify-content-end">
                    @if (\App\CentralLogics\Helpers::subscription_check())
                    @if(empty($rest_subscription))
                    <button class="btn btn--primary" data-toggle="modal" data-target="#subscription-modal">
                        <span class="ml-1">{{ translate('Add_Subscription_Package') }}</span> </button>
                    @endif
                    @if (isset($rest_subscription) &&  $rest_subscription->expiry_date <= Carbon\Carbon::today()->addDays('10'))
                    <button class="btn btn--warning my-2" data-toggle="modal" data-target="#subscription-modal">
                        <span class="ml-1">{{ translate('messages.renew_now') }}</span> </button>
                    @endif
                    @endif

                </div>
            </div>
            <ul class="nav nav-tabs page-header-tabs mb-0 mt-3">
                <li class="nav-item">
                    <a class="nav-link font-bold active" href="#" aria-disabled="true">{{ translate('messages.subscription_details') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('vendor.subscription.transcation') }}"  aria-disabled="true"> {{ translate('messages.transactions') }}</a>
                </li>
            </ul>

        </div>
        <!-- End Page Header -->
        @if (isset($rest_subscription))

        <div class="card __billing-subscription">
            <div class="card-body">
                <h4 class="main-title">{{translate('Billing')}}</h4>
                <div class="bg-FCFCFC d-flex flex-wrap">
                    <div class="__billing-item">
                        <img src="{{dynamicAsset('/public/assets/admin/img/subscription/1.png')}}" alt="img/subscription">
                        <div class="w-0 flex-grow pl-3 pl-sm-4">
                            <div class="info">{{ translate('messages.expire_date') }}</div>

                            @if ($restaurant?->restaurant_sub_trans?->transaction_status == 0)
                            <h4 class="subtitle" style="color: red"> {{ translate('messages.Payment_Unsuccessfull') }}</h4>
                            <span> {{ translate('messages.Payment Status') }} {{ translate($restaurant?->restaurant_sub_trans?->payment_status) }}</span>
                            @elseif($rest_subscription->status == 0)
                            <h4 class="subtitle" style="color: red"> {{ translate('messages.Package_Expired') }}</h4>
                            <span>{{ $rest_subscription->expiry_date->format('d M Y') }}</span>
                            @else
                            <h4 class="subtitle">{{ $rest_subscription->expiry_date->format('d M Y') }}</h4>
                            @endif
                        </div>
                    </div>
                    <div class="__billing-item">
                        <img src="{{dynamicAsset('/public/assets/admin/img/subscription/2.png')}}" alt="img/subscription">
                        <div class="w-0 flex-grow pl-3 pl-sm-4">
                            <div class="info">{{ translate('messages.Total_bill') }}</div>
                            <h4 class="subtitle">{{ \App\CentralLogics\Helpers::format_currency($total_bill) }}</h4>
                        </div>
                    </div>
                    <div class="__billing-item">
                        <img src="{{dynamicAsset('/public/assets/admin/img/subscription/3.png')}}" alt="img/subscription">
                        <div class="w-0 flex-grow pl-3 pl-sm-4">
                            <div class="info">{{ translate('messages.number_of_uses') }}</div>
                            <h4 class="subtitle">{{ $rest_subscription->total_package_renewed+1 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-2">
                    <h4 class="card-title mb-4">
                        <span class="card-header-icon">
                            <img class="w-20px" src="{{dynamicAsset('/public/assets/admin/img/subscription-plan.png')}}" alt="">
                        </span>
                        <span>{{translate('Subscription_Plan')}}</span>
                    </h4>
                    <div class="bg-FCFCFC __plan-details">
                        <div class="d-flex flex-wrap flex-md-nowrap justify-content-between __plan-details-top">
                            <div class="left">
                                <h3 class="name">{{ $rest_subscription->package->package_name }}</h3>
                                <div class="font-medium text--title"> {{$rest_subscription->package->text }}</div>
                            </div>
                            <h3 class="right">{{ \App\CentralLogics\Helpers::format_currency($rest_subscription->package->price ) }} / <small class="font-medium text--title">{{ $rest_subscription->package->validity }} {{ translate('messages.days') }}</small></h3>
                        </div>

                        <div class="check--item-wrapper mx-0 mb-0">
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" class="form-check-input  " checked>
                                    <label class="form-check-label  text-dark" for="account">
                                        @if ($rest_subscription->max_order == 'unlimited')
                                            {{ translate('messages.unlimited_orders') }}
                                        @else
                                            {{ $rest_subscription->package->max_order }} {{ translate('messages.Order') }}
                                    </label> <small style="color: {{ $rest_subscription->max_order < 10 ? 'red' : '' }}">
                                        (
                                        @if ($rest_subscription->max_order > 0)
                                        {{ $rest_subscription->max_order }}
                                        @else
                                        0
                                        @endif
                                        {{ translate('left') }})
                                    </small>
                                    @endif
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" {{ $rest_subscription->pos == 1 ? 'checked' : '' }}
                                        class="form-check-input  ">
                                    <label class="form-check-label qcont text-dark" for="account">{{ translate('messages.POS') }}
                                    </label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox"class="form-check-input  "
                                        {{ $rest_subscription->mobile_app == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label qcont text-dark"
                                        for="account">{{ translate('messages.Mobile_App') }}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox"class="form-check-input  "
                                        {{ $rest_subscription->self_delivery == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label  text-dark"
                                        for="account">{{ translate('messages.self_delivery') }}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" class="form-check-input  " checked>
                                    <label class="form-check-label  text-dark" for="account">
                                        @if ($rest_subscription->max_product == 'unlimited')
                                            {{ translate('messages.unlimited_product_Upload') }}
                                        @else
                                            {{ $rest_subscription->max_product }} {{ translate('messages.product_Upload') }}
                                    </label>
                                    @php($total_food=  $restaurant->foods()->count() ?? 0 )
                                    <small style="color: {{ ($rest_subscription->max_product - $total_food) < 10 ? 'red' : '' }}">
                                        (
                                        @if ($rest_subscription->max_product - $total_food > 0)
                                        {{ $rest_subscription->max_product - $total_food}}
                                        @else
                                        0
                                        @endif
                                        {{ translate('left') }})
                                    </small>
                                        @endif
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" class="form-check-input  "
                                        {{ $rest_subscription->chat == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label text-dark"
                                        for="account">{{ translate('messages.chat') }}</label>
                                </div>
                            </div>
                            <div class="check-item">
                                <div class="form-group form-check form--check">
                                    <input type="checkbox" class="form-check-input  "
                                        {{ $rest_subscription->review == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label  text-dark"
                                        for="account">{{ translate('messages.review') }}</label>
                                </div>
                            </div>
                        </div>

                    </div>

                    @if (\App\CentralLogics\Helpers::subscription_check())
                    <div class="__btn-container btn--container justify-content-end">
                        @if ($restaurant?->restaurant_sub_trans?->transaction_status == 0)
                            @if ($digital_payment == 1)
                            <a class="btn btn--warning"  href="{{ route('vendor.subscription.digital_payment_methods',$restaurant?->restaurant_sub_trans?->id) }}">
                                <span class="ml-1">{{ translate('Pay_Now') }}</span> </a>

                                @else
                                <a class="btn btn--secondary payment-warning"  href="#">
                                    <span class="ml-1">{{ translate('Pay_Now') }}</span> </a>

                            @endif
                        @endif

                        <button class="btn btn--primary" data-toggle="modal" data-target="#subscription-modal">
                        <span class="ml-1">{{ translate('Change / Renew Subscription Plan') }}</span> </button>
                    </div>
                    @endif

                </div>

            </div>

        </div>
        @else
        <div class="empty--data ">
            <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
            <h5  >
                {{translate('No_subscription_plan_available')}}
            </h5>
        </div>

        @endif


    </div>

    <!-- Subscrition Plan Modal -->
    <div class="modal fade __modal" id="subscription-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <h3 class="modal-title text-center">{{translate('Change_Subscription_Plan')}}</h3>

                <!-- Modal body -->
                <div class="modal-body overflow-hidden">
                    <div class="plan-slider owl-theme owl-carousel">
                    @forelse ($packages as $package)
                    <div class="__plan-item">
                        <!-- Active Plan Check -->
                        <input type="radio" name="package_id"  value="{{ $package->id }}" id="basic" {{ (isset($rest_subscription) && $rest_subscription->package_id == $package->id ) ?  'checked': ''}}  hidden>
                        <div class="__plan">
                            <div class="plan-header">
                                <h3 class="title">
                                    <span id="div_one_{{ $package->id }}" >{{ $package->package_name }}</span>
                                    <img class="check-plan-icon" src="{{dynamicAsset('/public/assets/landing/img/check3.svg')}}" alt="">
                                </h3>
                                <div class="duration">
                                    <strong>{{ translate('messages.fee') }} /</strong><span>{{ $package->validity }} {{ translate('messages.days') }}</span>
                                </div>
                                <h2 class="price">{{ \App\CentralLogics\Helpers::format_currency($package->price)}}</h2>
                            </div>
                            <ul class="plan-info">
                                @if ($package->pos)
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> POS
                                </li>
                                @endif
                                @if ($package->mobile_app)
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.mobile_app') }}
                                </li>
                                @endif
                                @if ($package->chat)
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.chatting_options') }}
                                </li>
                                @endif
                                @if ($package->review)
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.review_section') }}
                                </li>
                                @endif
                                @if ($package->self_delivery)
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.self_delivery') }}
                                </li>
                                @endif
                                @if ($package->max_order == 'unlimited')
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.Unlimited_Orders') }}
                                </li>
                                @else
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ $package->max_order }} {{ translate('messages.Orders') }}
                                </li>
                                @endif
                                @if ($package->max_product == 'unlimited')
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ translate('messages.Unlimited_uploads') }}
                                </li>
                                @else
                                <li>
                                    <img class="plan-info-icon" src="{{dynamicAsset('/public/assets/landing/img/check2.svg')}}" alt=""> {{ $package->max_product }} {{ translate('messages.uploads') }}
                                </li>
                                @endif



                            </ul>
                            <div class="text-center">
                                    @if (isset($rest_subscription)&& $rest_subscription->package_id == $package->id)
                                    <button data-id="{{ $package->id }}"
                                        data-target="#package_detail" id="package_detail" type="button" class="btn btn--warning text-white renew-btn package_detail">{{ translate('messages.Renew') }}</button>
                                    @else
                                    <button data-id="{{ $package->id }}"
                                        data-target="#package_detail" id="package_detail" type="button" class="btn btn--primary shift-btn package_detail">{{ translate('messages.Shift_in_this_plan') }}</button>
                                    @endif
                            </div>
                        </div>
                    </div>
                    @empty


                    <div class="img-responsive center-block d-block mx-auto">
                        <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
                        <h4  >
                        {{translate('No_subscription_plan_available')}}
                    </h4>
                    </div>
                    @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Subscrition Plan Modal 2 -->
    <div class="modal fade __modal" id="subscription-renew-modal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                    <div class="data_package" id="data_package">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')

<script type="text/javascript" src="{{dynamicAsset('/public/assets/landing/owl/dist/owl.carousel.min.js')}}"></script>
<script>
    "use strict";
    //Check Data
    $("input[name='package_id']").each(function(){
            if($(this).is(':checked')) {

                $('.__plan-item').find('.shift-btn').show()
                $('.__plan-item').find('.renew-btn').hide()

                $(this).closest('.__plan-item').addClass('active')
                $(this).closest('.__plan-item').find('.shift-btn').hide()
                $(this).closest('.__plan-item').find('.renew-btn').show()

                $($(this)).on('click', function(){
                    $('#subscription-modal').modal('hide')
                    $('#subscription-renew-modal').modal('show')
                })

            } else {
                $($(this)).on('click', function(){
                    $('#subscription-modal').modal('hide')
                    $('#subscription-change-plan-modal').modal('show')

                })
            }
        })

    // Plan Slider
    $('.plan-slider').owlCarousel({
            loop: false,
            margin: 30,
        center: true,
            responsiveClass:true,
            nav:false,
            dots:false,
            items: 3,
            autoplay: true,
            autoplayTimeout:1500,
            autoplayHoverPause:true,

            responsive:{
                0: {
                    items:1.1,
                    margin: 10,
                },
                375: {
                    items: 1.2,
                    margin: 30,
                },
                576: {
                    items:2.2,
                },
                768: {
                    items:2.2,
                    margin: 20,
                },
                992: {
                    items: 3,
                    margin: 30,
                },
                1200: {
                    items: 3,
                    margin: 37,
                }
            }
        })

    $(document).on('click', '.package_detail', function () {
            var id = $(this).attr('data-id');
            console.log(id);
            $.ajax({
                url: '{{url('/')}}/restaurant-panel/subscription/package_selected/'+id,
                method: 'get',
                beforeSend: function() {
                            $('#loading').show();
                            $('#subscription-modal').modal('hide')
                            },
                success: function(data){
                    $('#data_package').html(data.view);
                    $('#subscription-renew-modal').modal('show')
                },
                complete: function() {
                        $('#loading').hide();
                    },

            });
        });

    $('#package_selected').on('submit', function() {
            var formData = new FormData(this);
            console.log('working');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    $('#data_package').html(data.view);
                    $('#itemCount').html(data.total);
                    // $('.page-area').hide();
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });

    function status_change_alert(url, message, e) {
        e.preventDefault();
        Swal.fire({
            title: '{{ translate('Are_you_sure?') }}',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '{{ translate('no') }}',
            confirmButtonText: '{{ translate('yes') }}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href=url;
            }
        })
    }

    $('.payment-warning').on('click',function (event ){
    event.preventDefault();
    toastr.info(
        "{{ translate('messages.Currently,_there_are_no_payment_options_available._Please_contact_admin_regarding_any_payment_process_or_queries.') }}", {
            CloseButton: true,
            ProgressBar: true
        });
});

</script>

@endpush









