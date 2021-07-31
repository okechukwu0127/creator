@extends('layouts.app')

@section('title') {{trans('general.privacy_security')}} -@endsection

@section('content')
<section class="section section-sm">
    <div class="container">
      <div class="row justify-content-center text-center mb-sm">
        <div class="col-lg-8 py-5">
          <h2 class="mb-0 font-montserrat"><i class="bi bi-shield-check mr-2"></i> {{trans('general.privacy_security')}}</h2>
          <p class="lead text-muted mt-0">{{trans('general.desc_privacy')}}</p>
        </div>
      </div>
      <div class="row">

        @include('includes.cards-settings')

        <div class="col-md-6 col-lg-9 mb-5 mb-lg-0">

          @if (session('status'))
                  <div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                			<span aria-hidden="true">×</span>
                			</button>

                    {{ session('status') }}
                  </div>
                @endif

          @include('errors.errors-forms')

          <h5>{{ __('general.login_sessions') }}</h5>

          @if ($sessions)
              <div class="card mb-4">
                <div class="card-body">
                  <small class="w-100 d-block"><strong>{{ __('general.last_login_record') }}</strong></small>
                  <p class="card-text">{{ $sessions->user_agent }}</p>
                  <p>
                    <span>IP: {{ $sessions->ip_address }}

            <span class="w-100 d-block mt-2">
              @if ($current_session_id == $sessions->id)
                <button type="button" :disabled="true" class="btn btn-sm btn-primary e-none">{{ __('general.this_device') }}</button>
                @else
                  <form method="POST" action="{{ url('logout/session', $sessions->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-danger"><i class="feather icon-trash-2"></i> {{ __('general.delete') }}</button>
                  </form>
                @endif
            </span>
                  </p>
                </div>
              </div>
          @endif

          @if (auth()->user()->verified_id == 'yes')
            <form method="POST" action="{{ url('privacy/security') }}">

              @csrf

              <div class="form-group">
                <div class="text-muted btn-block mb-4">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" name="hide_profile" value="yes" @if (auth()->user()->hide_profile == 'yes') checked @endif id="customSwitch1">
                    <label class="custom-control-label switch" for="customSwitch1">{{ trans('general.hide_profile') }}</label>
                  </div>
                </div>

                <div class="text-muted btn-block mb-4">
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" name="hide_last_seen" value="yes" @if (auth()->user()->hide_last_seen == 'yes') checked @endif id="customSwitch2">
                    <label class="custom-control-label switch" for="customSwitch2">{{ trans('general.hide_last_seen') }}</label>
                  </div>
                </div>

              </div><!-- End form-group -->

              <button class="btn btn-1 btn-success btn-block" onClick="this.form.submit(); this.disabled=true; this.innerText='{{trans('general.please_wait')}}';" type="submit">{{trans('general.save_changes')}}</button>

            </form>
          @endif

        </div><!-- end col-md-6 -->
      </div>
    </div>
  </section>
@endsection
