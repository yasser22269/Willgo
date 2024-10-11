@extends('layouts.admin.app')

@section('title',translate('Package_list'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->

        <div class="page-header">
            <h1 class="page-header-title">
                <img src="{{dynamicAsset('/public/assets/admin/img/bill.png')}}" alt="" class="w-24 mr-2">
                {{ translate('messages.subscription_package_list') }}
                <span class="badge badge-soft-dark ml-2 px-2 badge-pill" id="itemCount" >{{$total}}</span>
            </h1>
        </div>

        <!-- Card -->
        <div class="card">
            <!-- Header -->
            <div class="card-header py-2 border-0">
                <div class="search--button-wrapper justify-content-end">
                    <span class="card-title"></span>
                    <form >
                        <!-- Search -->
                        <div class="input--group input-group input-group-merge input-group-flush">
                            <input id="datatableSearchqq" type="search" name="search" class="form-control" value="{{request()->get('search')}}"
                                    placeholder="{{ translate('Ex:_package_name') }}" aria-label="Search">
                            <button type="submit" class="btn btn--secondary">
                                <i class="tio-search"></i>
                            </button>
                        </div>
                        <!-- End Search -->
                    </form>


                    <!-- Unfold -->
                    <div class="hs-unfold mr-2">
                        <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle" href="javascript:;"
                            data-hs-unfold-options='{
                                "target": "#usersExportDropdown",
                                "type": "css-animation"
                            }'>
                            <i class="tio-download-to mr-1"></i> {{translate('messages.export')}}
                        </a>

                        <div id="usersExportDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-header">{{translate('messages.download_options')}}</span>
                            <a id="export-excel" class="dropdown-item"  href="{{ route('admin.subscription.package_list_export', ['type' => 'excel', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{dynamicAsset('public/assets/admin')}}/svg/components/excel.svg"
                                        alt="Image Description">
                                {{translate('messages.excel')}}
                            </a>
                            <a id="export-csv" class="dropdown-item"  href="{{ route('admin.subscription.package_list_export', ['type' => 'csv', request()->getQueryString()]) }}">
                                <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="{{dynamicAsset('public/assets/admin')}}/svg/components/placeholder-csv-format.svg"
                                        alt="Image Description">
                                {{translate('messages.csv')}}
                            </a>
                        </div>
                    </div>
                    <!-- End Unfold -->
                    <a class="btn btn--primary" href="{{ route('admin.subscription.create') }}">
                        <i class="tio-add"></i> {{ translate('messages.add_subcription_package') }}
                    </a>
                </div>
                <!-- End Row -->
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table id="datatable"
                       class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                     "columnDefs": [{
                        "targets": [1,5,6],
                        "orderable": false
                      }],
                     "order": [],
                     "info": {
                       "totalQty": "#datatableWithPaginationInfoTotalQty"
                     },
                     "search": "#datatableSearch",
                     "entries": "#datatableEntries",
                     "pageLength": 25,
                     "isResponsive": false,
                     "isShowPaging": false,
                     "paging":false
                   }'>
                    <thead class="thead-light">
                    <tr>
                        <th class="">
                            {{ translate('messages.sl') }}
                        </th>
                        <th class="table-column-pl-0">{{translate('Package_Name')}}</th>
                        <th>{{translate('messages.Pricing')}}</th>
                        <th>{{translate('messages.duration') }}</th>
                        <th>{{translate('messages.total_sell')}}</th>
                        <th class="text-center">{{translate('messages.status')}}</th>
                        <th class="text-center">{{translate('messages.actions')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                        @include('admin-views.subscription.partials._table')
                    </tbody>
                </table>
            </div>
            @if(count($packages) === 0)
            <div class="empty--data">
                <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
                <h5>
                    {{translate('no_data_found')}}
                </h5>
            </div>
            @endif
            <!-- End Table -->
            <div class="page-area px-4 pb-3">
                <div class="d-flex align-items-center justify-content-end">
                    <div>
                        {!! $packages->links() !!}
                    </div>
                </div>
            </div>
            <!-- End Footer -->

        </div>
        <!-- End Card -->
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

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
        $(document).on('ready', function () {

            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#datatable'), {
            });

        });

    </script>

@endpush
