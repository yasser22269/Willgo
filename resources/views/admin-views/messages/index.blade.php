@extends('layouts.admin.app')

@section('title', translate('Messages'))


@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.conversation_list') }}</h1>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card">
                    <div class="card-header border-0">
                        <div class="input-group input---group">
                            <div class="input-group-prepend border-inline-end-0">
                                <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                            </div>
                            <input type="search" class="form-control border-inline-start-0 pl-1" id="serach" placeholder="{{ translate('messages.search') }}" aria-label="Username" aria-describedby="basic-addon1" autocomplete="off">
                        </div>
                    </div>
                    <!-- Body -->
                    <div class="card-body p-0 initial-19" id="conversation-list">
                        <div class="border-bottom"></div>
                        @include('admin-views.messages.data')
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
            <div class="col-lg-8 col-nd-6" id="admin-view-conversation">
                <div class="text-center mt-2">
                    <h4 class="initial-29">{{ translate('messages.view_conversation') }}
                    </h4>
                </div>
            </div>
        </div>
        <!-- End Row -->
    </div>

@endsection

@push('script_2')
<script src="{{ dynamicAsset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
<script src="{{ dynamicAsset('public/assets/admin/js/emojionearea.js') }}"></script>

<script>
    "use strict";

    $('.view-admin-conv').on('click', function (){
        let url = $(this).data('url');
        let id_to_active = $(this).data('active-id');
        let conv_id = $(this).data('conv-id');
        let sender_id = $(this).data('sender-id');
        viewAdminConvs(url, id_to_active, conv_id, sender_id);
    })

    function viewAdminConvs(url, id_to_active, conv_id, sender_id) {
            $('.customer-list').removeClass('conv-active');
            $('#' + id_to_active).addClass('conv-active');
            let new_url= "{{ route('admin.message.list') }}" + '?conversation=' + conv_id+ '&user=' + sender_id;
            $.get({
                url: url,
                success: function(data) {
                    window.history.pushState('', 'New Page Title', new_url);
                    $('#admin-view-conversation').html(data.view);
                    conversationList();
                }
            });

        }
        let page = 1;
        $('#conversation-list').scroll(function() {
            if ($('#conversation-list').scrollTop() + $('#conversation-list').height() >= $('#conversation-list')
                .height()) {
                page++;
                loadMoreData(page);
            }
        });

        function loadMoreData(page) {
            $.ajax({
                    url: "{{ route('admin.message.list') }}" + '?page=' + page,
                    type: "get",
                    beforeSend: function() {

                    }
                })
                .done(function(data) {
                    if (data.html == " ") {
                        return;
                    }
                    $("#conversation-list").append(data.html);
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    alert('server not responding...');
                });
        }

        function fetch_data(page, query) {
            $.ajax({
                url: "{{ route('admin.message.list') }}" + '?page=' + page + "&key=" + query,
                success: function(data) {
                    $('#conversation-list').empty();
                    $("#conversation-list").append(data.html);
                }
            })
        }

        $(document).on('keyup', '#serach', function() {
            let query = $('#serach').val();
            fetch_data(page, query);
        });

        document.getElementById('serach').addEventListener('input', function(event) {
            if (this.value === "") {
                let query = $('#serach').val();
                fetch_data(page, query);
            }
        });
    </script>
@endpush
