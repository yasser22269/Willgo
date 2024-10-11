@extends('layouts.landing.app')
@section('title', translate('messages.restaurant_registration'))
@push('css_or_js')
<link rel="stylesheet" href="{{ dynamicAsset('public/assets/landing') }}/css/style.css" />
@endpush
@section('content')
<!-- Page Header Gap -->
<div class="h-148px"></div>
<!-- Page Header Gap -->

<section class="m-0 landing-inline-1 section-gap">
    <div class="container">
        <!-- Page Header -->
        <div class="step__header">
            <h4 class="title">{{ translate('messages.Restaurant_registration_application') }}</h4>
            <div class="step__wrapper">
                <div class="step__item active">
                    <span class="shapes"></span>
                    {{ translate('messages.general_information') }}
                </div>
                <div class="step__item active">
                    <span class="shapes"></span>
                    {{ translate('messages.business_plan') }}
                </div>
                <div class="step__item active">
                    <span class="shapes"></span>
                    {{ translate('messages.complete') }}
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="card __card">
            <div class="card-body">
                <div class="succeed--status">
                    <img class="img"
                    src="{{ $logo }}"
                    alt="image">
                    <h4 class="title">{{ translate('Congratulations!') }}</h4>
                    <h6 class="subtitle">
                        {{ translate('messages.Your_registration_has_been_completed_successfully.') }}
                    </h6>
                    <div>
                        {{ translate('messages.You_will_receive_a_confirmation_email_within') }}
                        <strong> {{ translate('messages.48_hour') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
