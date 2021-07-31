@extends('layouts.app')

@section('title'){{trans('general.messages')}} -@endsection

@section('css')
  <script type="text/javascript">
      var subscribed_active = {{ $subscribedToYourContent || $subscribedToMyContent ? 'true' : 'false' }};
      var user_id_chat = {{ $user->id }};
      var msg_count_chat = {{ $allMessages }};
  </script>
@endsection

@section('content')
<section class="section section-sm">
    <div class="container py-5">
      <div class="row justify-content-center">

  <div class="col-md-6 col-lg-9 mb-5 mb-lg-0">

  <div class="card">
    <div class="card-header bg-white">
      <div class="media">
        <a href="{{url('messages')}}" title="{{ trans('general.back_messages') }}" class="mr-3"><i class="fa fa-arrow-left"></i></a>
        <a href="{{url($user->username)}}" class="mr-3">
          <span class="position-relative user-status @if (Cache::has('is-online-' . $user->id)) user-online @else user-offline @endif d-block">
            <img src="{{Helper::getFile(config('path.avatar').$user->avatar)}}" class="rounded-circle" width="40" height="40">
          </span>
      </a>

        <div class="media-body">
          <h6 class="m-0">
            <a href="{{url($user->username)}}">{{$user->name}}</a>
          </h6>

          @if ($user->hide_last_seen == 'no')
           <small>{{ trans('general.active') }}</small>


           <span id="timeAgo">
             <small class="timeAgo @if (Cache::has('is-online-' . $user->id)) display-none @endif" id="lastSeen" data="{{ date('c', strtotime($user->last_seen ?? $user->date)) }}"></small>
            </span>
          @else
            {{'@'.$user->username}}
            @endif

        </div>

        @if ($messages->count() != 0)
        <a href="javascript:void(0);" class="text-muted float-right" id="dropdown_options" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<i class="fa fa-ellipsis-h"></i>
				</a>

        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_options">
					{!! Form::open([
						'method' => 'POST',
						'url' => "conversation/delete/$user->id",
						'class' => 'd-inline'
					]) !!}

					{!! Form::button(trans('general.delete'), ['class' => 'dropdown-item actionDelete']) !!}
					{!! Form::close() !!}
	      </div>
      @endif

      </div>

    </div>

    <div class="content px-4 py-3 d-scrollbars" id="contentDIV" style="height: 400px; position: relative; overflow: auto;" data="{{$user->id}}">

      @if ($allMessages != 0)
      <div class="flex-column d-flex justify-content-center text-center h-100">
        <div class="w-100" id="loadAjaxChat">
          <div class="spinner-border text-primary" role="status"></div>
        </div>
      </div>
    @endif
      </div><!-- contentDIV -->

          <div class="card-footer bg-white position-relative">

          @if ($subscribedToYourContent || $subscribedToMyContent)

            <div class="w-100 display-none" id="previewFile">
              <div class="previewFile d-inline"></div>
              <a href="javascript:;" class="text-danger" id="removeFile"><i class="fa fa-times-circle"></i></a>
            </div>

            <div class="progress-upload-cover" style="width: 0%; top:0;"></div>

            <div class="blocked display-none"></div>

            <!-- Alert -->
            <div class="alert alert-danger my-3" id="errorMsg" style="display: none;">
             <ul class="list-unstyled m-0" id="showErrorMsg"></ul>
           </div><!-- Alert -->

            <form action="{{url('message/send')}}" method="post" accept-charset="UTF-8" id="formSendMsg" enctype="multipart/form-data">
              <input type="hidden" name="id_user" id="id_user" value="{{$user->id}}">
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
              <input type="file" name="photo" id="file" accept="image/*,video/mp4,video/x-m4v,video/quicktime,audio/mp3" style="visibility: hidden;">
              <input type="file" name="zip" id="zipFile" accept="application/x-zip-compressed" class="visibility-hidden">

              <div class="media align-items-center">

                <div class="mr-3" style="font-size:25px">
                  <a href="javascript:;" onclick="$('#file').trigger('click')"><i class="far fa-image"></i></a>
                </div>

                <div class="mr-3" style="font-size:23px">
                  <a href="javascript:;" title="{{trans('general.upload_file_zip')}}" onclick="$('#zipFile').trigger('click')"><i class="far fa-file-archive"></i></a>
                </div>

              @if ($user->verified_id == 'yes')
              <div class="mr-2" style="font-size:23px">
                <a href="javascript:void(0);" data-toggle="modal" title="{{trans('general.tip')}}" data-target="#tipForm" data-cover="{{Helper::getFile(config('path.cover').$user->cover)}}" data-avatar="{{Helper::getFile(config('path.avatar').$user->avatar)}}" data-name="{{$user->name}}" data-userid="{{$user->id}}">
                  <i class="fa fa-donate mr-1 mr-lg-0"></i>
                </a>
              </div>
              @endif

                <div class="w-100 mr-2">
                  <textarea class="form-control textareaAutoSize border-0" data-post-length="{{$settings->update_length}}" rows="1" placeholder="{{trans('general.write_something')}}" id="message" name="message"></textarea>
                </div>

          <div class="media-body">

            <button type="submit" id="button-reply-msg" disabled data-send="{{ trans('auth.send') }}" data-wait="{{ trans('general.send_wait') }}" class="btn btn-primary rounded-circle e-none px-3">
              <i class="far fa-paper-plane"></i>
            </button>
            </div>
            </div>
        </form>
      @else
        <div class="alert alert-primary m-0 alert-dismissible fade show" role="alert">
          <i class="fa fa-info-circle mr-2"></i>
        {!! trans('general.show_form_msg_error_subscription_', ['user' => '<a href="'.url($user->username).'" class="link-border text-white">'.$user->first_name.'</a>']) !!}
      </div>
        @endif

      </div><!-- card footer -->
    </div><!-- card -->
    </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection

@section('javascript')
<script src="{{ asset('public/js/messages.js') }}?v={{$settings->version}}"></script>
@endsection
