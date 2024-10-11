<div class="card h-100">
    <!-- Header -->
    <div class="card-header">
        <div class="chat-user-info w-100 d-flex align-items-center">
            <div class="chat-user-info-img">
                <img class="avatar-img onerror-image"
                     src="{{$user['image_full_url']}}"
                     data-onerror-image="{{dynamicAsset('public/assets/admin')}}/img/160x160/img1.jpg"
                     alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 text-capitalize">
                    {{$user['f_name'].' '.$user['l_name']}}</h5>
                <span dir="ltr"><a href="tel:{{ $user['phone'] }}"> {{ $user['phone'] }}</a></span>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn shadow-none" data-toggle="dropdown">
                <img src="{{dynamicAsset('/public/assets/admin/img/ellipsis.png')}}" alt="">
            </button>
            @if ($user?->user)

            <ul class="dropdown-menu conv-dropdown-menu">
                <li>
                    <a href="{{ route('admin.customer.view', [$user->user->id]) }}"
                        >{{ translate('view_order_list') }}</a>
                </li>
            </ul>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="scroll-down">
            @foreach($convs as $con)
                @if($con->sender_id == $receiver->id)
                    <div class="pt1 pb-1">
                        <div class="conv-reply-1">
                                <h5>{{$con->message}}</h5>
                                @if($con->file!=null)
                                @foreach ($con->file_full_url as $img)
                                <br>
                                    <img class="w-100"

                                    src="{{$img }}"
                                    >
                                    @endforeach
                                @endif
                        </div>
                        <div class="pl-1">
                            <small>
                                {{ \App\CentralLogics\Helpers::time_date_format($con->created_at) }}
                            </small>
                        </div>
                    </div>
                @else
                    <div class="pt-1 pb-1">
                        <div class="conv-reply-2">
                            <h5>{{$con->message}}</h5>
                            @if($con->file!=null)
                            @foreach ($con->file_full_url as $img)
                            <br>
                                <img class="w-100"
                                src="{{ $img }}"
                                >
                                @endforeach
                            @endif
                        </div>
                        <div class="text-right pr-1">
                            <small>   {{ \App\CentralLogics\Helpers::date_format($con->created_at) }}  {{ \App\CentralLogics\Helpers::time_format($con->created_at) }}</small>
                            @if ($con->is_seen == 1)
                            <span class="text-primary"><i class="tio-checkmark-circle"></i></span>
                            @else
                            <span><i class="tio-checkmark-circle-outlined"></i></span>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
            <div id="scroll-here"></div>
        </div>

    </div>
    <!-- Body -->
    <div class="card-footer border-0 conv-reply-form">

        <form action="javascript:" method="post" id="reply-form" enctype="multipart/form-data">
            @csrf
            <div class="quill-custom_">
                <label for="msg" class="layer-msg">

                </label>
                <textarea class="form-control pr--180" id="msg" rows = "1" name="reply"></textarea>
                <div class="upload__box">
                    <div class="upload__img-wrap"></div>
                    <div id="file-upload-filename" class="upload__file-wrap"></div>
                    <div class="upload-btn-grp">
                        <label class="m-0">
                            <img src="{{dynamicAsset('/public/assets/admin/img/gallery.png')}}" alt="">
                            <input type="file" name="images[]" class="d-none upload_input_images" data-max_length="2" accept="image/*"  multiple="" >
                        </label>
                        <label class="m-0 emoji-icon-hidden">
                            <img src="{{dynamicAsset('/public/assets/admin/img/emoji.png')}}" alt="">
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn--primary con-reply-btn">{{translate('messages.send')}}
                </button>
            </div>
        </form>
    </div>
</div>

<script src="{{dynamicAsset('public/assets/admin')}}/js/view-pages/common.js"></script>
<script>
    "use strict";
    $(document).ready(function() {
        $("#msg").emojioneArea({
            pickerPosition: "top",
            tonesStyle: "bullet",
                events: {
                    keyup: function (editor, event) {
                        console.log(editor.html());
                        console.log(this.getText());
                    }
                }
            });
    });

    $(document).ready(function () {
    // Image Upload
    jQuery(document).ready(function () {
        ImgUpload();
    });
    function ImgUpload() {
    let imgWrap = "";
    let imgArray = [];

    $('.upload_input_images').each(function () {
        $(this).on('change', function (e) {
        imgWrap = $(this).closest('.upload__box').find('.upload__img-wrap');
        let maxLength = $(this).attr('data-max_length');

        let files = e.target.files;
        let filesArr = Array.prototype.slice.call(files);
        let iterator = 0;
        filesArr.forEach(function (f, index) {

            if (!f.type.match('image.*')) {
            return;
            }

            if (imgArray.length > maxLength) {
            return false
            } else {
            let len = 0;
            for (let i = 0; i < imgArray.length; i++) {
                if (imgArray[i] !== undefined) {
                len++;
                }
            }
            if (len > maxLength) {
                return false;
            } else {
                imgArray.push(f);

                let reader = new FileReader();
                reader.onload = function (e) {
                let html = "<div class='upload__img-box'><div style='background-image: url(" + e.target.result + ")' data-number='" + $(".upload__img-close").length + "' data-file='" + f.name + "' class='img-bg'><div class='upload__img-close'></div></div></div>";
                imgWrap.append(html);
                iterator++;
                }
                reader.readAsDataURL(f);
            }
            }
        });
        });
    });

    $('body').on('click', ".upload__img-close", function (e) {
        let file = $(this).parent().data("file");
        for (let i = 0; i < imgArray.length; i++) {
        if (imgArray[i].name === file) {
            imgArray.splice(i, 1);
            break;
        }
        }
        $(this).parent().parent().remove();
    });
    }

    //File Upload
    $('#file-upload').change(function(e){
        let fileName = e.target.files[0].name;
        $('#file-upload-filename').text(fileName)
    });

    });
    $(document).ready(function () {
        $('.scroll-down').animate({
            scrollTop: $('#scroll-here').offset().top
        },0);
    });


    $(function() {
        $("#coba").spartanMultiImagePicker({
            fieldName: 'images[]',
            maxCount: 3,
            rowHeight: '55px',
            groupClassName: 'attc--img',
            maxFileSize: '',
            placeholderImage: {
                image: '{{ dynamicAsset('public/assets/admin/img/attatchments.png') }}',
                width: '100%'
            },
            dropFileLabel: "Drop Here",
            onAddRow: function(index, file) {

            },
            onRenderedPreview: function(index) {

            },
            onRemoveRow: function(index) {

            },
            onExtensionErr: function(index, file) {
                toastr.error('{{ translate('messages.please_only_input_png_or_jpg_type_file') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function(index, file) {
                toastr.error('{{ translate('messages.file_size_too_big') }}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    });


    $('#reply-form').on('submit', function() {
        $('button[type=submit], input[type=submit]').prop('disabled',true);
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{ route('admin.message.store', [$user->user_id]) }}',
                data: $('reply-form').serialize(),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data.errors && data.errors.length > 0) {

                        if (data.errors[1] && data.errors[1].code == 'images') {
                            toastr.error(data.errors[1].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        } else {

                            $('button[type=submit], input[type=submit]').prop('disabled',false);
                            toastr.error('{{ translate('Write_something_to_send_massage!') }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }

                    }else{

                        toastr.success('Message sent', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        $('#admin-view-conversation').html(data.view);
                    }
                },
                error() {
                    toastr.error('{{ translate('Write_something_to_send_massage!') }}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
</script>
