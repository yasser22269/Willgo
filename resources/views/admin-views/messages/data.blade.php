@foreach($conversations as $conv)
    @php($user= $conv->sender_type == 'admin' ? $conv->receiver :  $conv->sender)
    @if ($user)
    @php($unchecked=($conv->last_message?->sender_id == $user->id) ? $conv->unread_message_count : 0)

    <div class="chat-user-info d-flex border-bottom p-3 align-items-center customer-list view-admin-conv {{$unchecked ? 'new-msg ' : ''}} {{$unchecked ? 'conv-active' : ''}}"
            {{-- data-url="{{route('admin.message.view',['conversation_id'=>$conv->id,'user_id'=>$user->id])}}"
            data-active-id="customer-{{$user->id}}"
            data-conv-id="{{ $conv->id }}"
            data-sender-id="{{ $user->id }}" --}}

            onclick="viewAdminConvs('{{route('admin.message.view',['conversation_id'=>$conv->id,'user_id'=>$user->id])}}','customer-{{$user->id}}','{{ $conv->id }}','{{ $user->id }}')"
            id="customer-{{$user->id}}">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img onerror-image"
                     src="{{ $user['image_full_url']}}"
                     data-onerror-image="{{dynamicAsset('public/assets/admin')}}/img/160x160/img1.jpg"
                     alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3">{{$user['f_name'].' '.$user['l_name']}}</span> <span
                        class="{{$unchecked ? 'badge badge-info' : ''}}">{{$unchecked ? $unchecked : ''}}</span>
                        <small>
                        {{ \App\CentralLogics\Helpers::time_format($conv->last_message?->created_at) }}
                            </small>
                </h5>
                <small> {{ $user['phone'] }}</small>
                <div class="text-title">{{ Str::limit($conv->last_message?->message ??'', 35, '...') }}</div>
            </div>
        </div>
    @else
        <div
            class="chat-user-info d-flex border-bottom p-3 align-items-center customer-list">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img"
                        src='{{dynamicAsset('public/assets/admin')}}/img/160x160/img1.jpg'
                        alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3">{{translate('messages.user_not_found')}}</span>
                </h5>
            </div>
        </div>
    @endif
@endforeach
<script src="{{dynamicAsset('public/assets/admin')}}/js/view-pages/common.js"></script>

