@php($currency=\App\Models\BusinessSetting::where(['key'=>'currency'])->first()->value)

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>
        @yield('title')
    </title>
    <!-- SEO Meta Tags-->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <!-- Viewport-->
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon and Touch Icons-->
    <link rel="shortcut icon" href="favicon.ico">
    <!-- Font -->
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/vendor/icon-set/style.css">
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/css/custom.css">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin/css/style.css')}}">

    <script
        src="{{dynamicAsset('public/assets/admin')}}/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js"></script>
    <link rel="stylesheet" href="{{dynamicAsset('public/assets/admin')}}/css/toastr.css">
    {{--stripe--}}
    <script src=""></script>
    <script src="https://js.stripe.com/v3/"></script>
    {{--stripe--}}
</head>
<!-- Body-->
<body>
<!-- Page Content-->
<div class="container pb-5 mb-2 mb-md-4">
    <div class="row">
        <div class="col-md-12">
            <div id="payment-redirection" class="initial-hidden">
                <div class="loading--2">
                    <div class="text-center"><h1>{{translate('messages.Redirecting_to_the_payment_page')}}......</h1></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 mb-5 pt-5">
            <center class="">
                <h1>Payment method</h1>
            </center>
        </div>
        @php($order=\App\Models\Order::find(session('order_id')))
        <section class="col-lg-12">
            <div class="checkout_details mt-3">
                <div class="row">

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('ssl_commerz_payment'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card" onclick="$('#ssl-form').submit()">
                                <div class="card-body pt-2 h-70px">
                                    <form action="{{ route('pay-ssl',request()->getQueryString()) }}" method="POST" class="needs-validation" id="ssl-form">
                                        <input type="hidden" value="{{ csrf_token() }}" name="_token"/>
                                        <button class="btn btn-block click-if-alone" type="submit">
                                            <img width="100"
                                                 src="{{dynamicAsset('public/assets/admin/img/sslcomz.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('razor_pay'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-1 h-70px">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('razor_pay'))
                                    @php($order=\App\Models\Order::find(session('order_id')))
                                    <form action="{!!route('payment-razor',['order_id'=>$order['id']])!!}" method="POST">
                                    @csrf
                                    <!-- Note that the amount is in paise = 50 INR -->
                                        <!--amount need to be in paisa-->
                                        <script src="https://checkout.razorpay.com/v1/checkout.js"
                                                data-key="{{ Config::get('razor.razor_key') }}"
                                                data-amount="{{($order->order_amount - $order->partially_paid_amount)*100}}"
                                                data-buttontext="Pay {{$order->order_amount - $order->partially_paid_amount}} {{\App\CentralLogics\Helpers::currency_code()}}"
                                                data-name="{{\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()->value}}"
                                                data-description="{{$order['id']}}"
                                                data-image="{{dynamicStorage('storage/app/public/business/'.\App\Models\BusinessSetting::where(['key'=>'logo'])->first()->value)}}"
                                                data-prefill.name="{{$order->customer->f_name}}"
                                                data-prefill.email="{{$order->customer->email}}"
                                                data-theme.color="#ff7529">
                                        </script>
                                    </form>
                                    <button class="btn btn-block click-if-alone" type="button"
                                            onclick="{{\App\CentralLogics\Helpers::currency_code()=='INR'?"$('.razorpay-payment-button').click()":"toastr.error('Your currency is not supported by Razor Pay.')"}}">
                                        <img width="100"
                                             src="{{dynamicAsset('public/assets/admin/img/razorpay.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paypal'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pb-0 pt-1 h--70px">
                                    <form class="needs-validation" method="GET"
                                          action="{{route('paypal.pay')}}">
                                        <button class="btn btn-block click-if-alone" type="submit">
                                            <img width="100"
                                                 src="{{dynamicAsset('public/assets/admin/img/paypal.png')}}"/>
                                        </button>
                                        <input type="hidden" name="order_id" value="{{$order->id}}">
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('stripe'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body py-0 h-70px">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('stripe'))
                                    <button class="btn btn-block click-if-alone" type="button" id="checkout-button">
                                        <img width="100" src="{{dynamicAsset('public/assets/admin/img/stripe.png')}}"/>
                                    </button>

                                    <script type="text/javascript">
                                        // Create an instance of the Stripe object with your publishable API key
                                        var stripe = Stripe('{{$config['published_key']}}');
                                        var checkoutButton = document.getElementById("checkout-button");
                                        checkoutButton.addEventListener("click", function () {
                                            fetch("{{route('pay-stripe',['order_id'=>$order->id])}}", {
                                                method: "GET",
                                            }).then(function (response) {
                                                console.log(response)
                                                return response.text();
                                            }).then(function (session) {
                                                console.log(JSON.parse(session).id)
                                                return stripe.redirectToCheckout({sessionId: JSON.parse(session).id});
                                            }).then(function (result) {
                                                if (result.error) {
                                                    alert(result.error.message);
                                                }
                                            }).catch(function (error) {
                                                console.error("Error:", error);
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    @endif
        
                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paystack'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body py-0 h--70px">
                                    <form id="paymentForm">
                                        <input type="hidden" id="publicKey" value="{{ $config['publicKey'] }}">
                                        <input type="hidden" value="{{ $order->customer->email }}" id="email-address" required />
                                        <input type="hidden" value="{{ round($order->order_amount - $order->partially_paid_amount )}}" id="amount" required />
                                        <button class="btn btn-block click-if-alone" type="submit" onclick="payWithPaystack()">
                                          <img width="100" src="{{dynamicAsset('public/assets/admin/img/paystack.png')}}"/>
                                        </button>
                                      </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('senang_pay'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-1 h-70px">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('senang_pay'))
                                    @php($user=\App\Models\User::where(['id'=>$order['user_id']])->first())
                                    @php($secretkey = $config['secret_key'])
                                    @php($data = new \stdClass())
                                    @php($data->merchantId = $config['merchant_id'])
                                    @php($data->detail = 'payment')
                                    @php($data->order_id = $order->id)
                                    @php($data->amount = $order->order_amount - $order->partially_paid_amount)
                                    @php($data->name = $user->f_name.' '.$user->l_name)
                                    @php($data->email = $user->email)
                                    @php($data->phone = $user->phone)
                                    @php($data->hashed_string = md5($secretkey . urldecode($data->detail) . urldecode($data->amount) . urldecode($data->order_id)))

                                    <form name="order" method="post"
                                          action="https://{{env('APP_MODE')=='live'?'app.senangpay.my':'sandbox.senangpay.my'}}/payment/{{$config['merchant_id']}}">
                                        <input type="hidden" name="detail" value="{{$data->detail}}">
                                        <input type="hidden" name="amount" value="{{$data->amount}}">
                                        <input type="hidden" name="order_id" value="{{$data->order_id}}">
                                        <input type="hidden" name="name" value="{{$data->name}}">
                                        <input type="hidden" name="email" value="{{$data->email}}">
                                        <input type="hidden" name="phone" value="{{$data->phone}}">
                                        <input type="hidden" name="hash" value="{{$data->hashed_string}}">
                                    </form>

                                    <button class="btn btn-block click-if-alone" type="button"
                                            onclick="{{\App\CentralLogics\Helpers::currency_code()=='MYR'?"document.order.submit()":"toastr.error('Your currency is not supported by Senang Pay.')"}}">
                                        <img width="100"
                                             src="{{dynamicAsset('public/assets/admin/img/senangpay.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('flutterwave'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-2 h-70px">
                                    <form method="POST" action="{{ route('flutterwave_pay',request()->getQueryString()) }}">
                                        {{ csrf_field() }}

                                        <button class="btn btn-block click-if-alone" type="submit">
                                            <img width="200"
                                                 src="{{dynamicAsset('public/assets/admin/img/fluterwave.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('mercadopago'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-2 h-70px">
                                    <a class="btn btn-block click-if-alone" href="{{route('mercadopago.index',request()->getQueryString())}}">
                                        <img width="150"
                                                src="{{dynamicAsset('public/assets/admin/img/MercadoPago_(Horizontal).svg')}}"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paymob_accept'))
                    @if($config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-100px">
                                    <form class="needs-validation" method="POST" id="payment-form-paymob"
                                        action="{{route('paymob-credit')}}">
                                        {{ csrf_field() }}
                                        <button class="btn btn-block click-if-alone" type="submit">
                                            <img width="150"
                                                src="{{dynamicAsset('public/assets/admin/img/paymob.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('bkash'))
                    @if(isset($config)  && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h--100px">
                                    <a class="btn btn-block click-if-alone" href="{{route('bkash-make-payment',request()->getQueryString())}}">
                                        <img class="initial--40" src="{{dynamicAsset('public/assets/admin/img/bkash.png')}}"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paytabs'))
                    @if(isset($config)  && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-100px">
                                    <button class="btn btn-block click-if-alone mt-n-11px" onclick="location.href='{{route('paytabs-payment')}}'">
                                        <img width="150" src="{{dynamicAsset('public/assets/admin/img/paytabs.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paytm'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-100px">
                                    <a class="btn btn-block click-if-alone" href="{{route('paytm-payment',request()->getQueryString())}}">
                                        <img class="initial-70" src="{{dynamicAsset('public/assets/admin/img/paytm.png')}}"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('liqpay'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-100px">
                                    <a class="btn btn-block click-if-alone" href="{{route('liqpay-payment',request()->getQueryString())}}">
                                        <img class="initial-70 mt-0" src="{{dynamicAsset('public/assets/admin/img/liqpay4.png')}}"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<!-- JS Front -->
<script src="{{dynamicAsset('public/assets/admin')}}/js/custom.js"></script>
<script src="{{dynamicAsset('public/assets/admin')}}/js/vendor.min.js"></script>
<script src="{{dynamicAsset('public/assets/admin')}}/js/theme.min.js"></script>
<script src="{{dynamicAsset('public/assets/admin')}}/js/sweet_alert.js"></script>
<script src="{{dynamicAsset('public/assets/admin')}}/js/toastr.js"></script>
<script src="{{dynamicAsset('public/assets/admin')}}/js/bootstrap.min.js"></script>

{!! Toastr::message() !!}



<script>
    setTimeout(function () {
        $('.stripe-button-el').hide();
        $('.razorpay-payment-button').hide();
        $('#payment-redirection').hide();
    }, 10)
</script>

<script src="https://js.paystack.co/v2/inline.js"></script>

<script>
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener("submit", payWithPaystack, false);
    function payWithPaystack(e) {
        e.preventDefault();
        showLoader();
        const channels = ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'];
        const paystack = new PaystackPop();
        paystack.newTransaction({
            key: document.getElementById("publicKey").value, // Replace with your public key
            email: document.getElementById("email-address").value,
            amount: document.getElementById("amount").value * 100,
            ref: '{{$order->id}}',
            channels: channels,
            currency: "{{$currency}}",

            onSuccess: (transaction) => {
                $("#loading").show();
                window.location.href = "{{ route('paystack-callback') }}?reference=" + transaction.reference;

            },
            onCancel: () => {
                window.location.href = "{{ route('paystack-callback') }}?reference=" + {{$order->id}};
            }
        });
    }
    
    function showLoader() {
        const loader = document.createElement("div");
        loader.style.position = "fixed";
        loader.style.top = "0";
        loader.style.left = "0";
        loader.style.width = "100%";
        loader.style.height = "100%";
        loader.style.background = "rgba(0, 0, 0, 0.5)";
        loader.style.display = "flex";
        loader.style.justifyContent = "center";
        loader.style.alignItems = "center";
        const spinner = document.createElement("div");
        spinner.className = "spinner-border text-light";
        spinner.setAttribute("role", "status");
        loader.appendChild(spinner);
        document.body.appendChild(loader);
    }
</script>

<script>
    function click_if_alone() {
        let total = $('.checkout_details .click-if-alone').length;
        if (Number.parseInt(total) == 1) {
            $('.click-if-alone')[0].click()
            $('#payment-redirection').show();
        }
    }
    @if(!Session::has('toastr::messages'))
        click_if_alone();
    @endif
</script>
</body>
</html>
