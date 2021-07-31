<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="@yield('description_custom')@if(!Request::route()->named('seo') && !Request::route()->named('profile')){{trans('seo.description')}}@endif">
  <meta name="keywords" content="@yield('keywords_custom'){{ trans('seo.keywords') }}" />
  <meta name="theme-color" content="{{ auth()->check() && auth()->user()->dark_mode == 'on' ? '#303030' : $settings->color_default }}">
  <title>{{ Auth::check() && User::notificationsCount() ? '('.User::notificationsCount().') ' : '' }}@section('title')@show @if( isset( $settings->title ) ){{$settings->title}}@endif</title>
  <!-- Favicon -->
  <link href="{{ url('public/img', $settings->favicon) }}" rel="icon">

  @laravelPWA

  @include('includes.css_general')

  @yield('css')

 @if($settings->google_analytics != '')
  {!! $settings->google_analytics !!}
  @endif
</head>

<body>
  <div class="btn-block text-center showBanner padding-top-10 pb-3 display-none">
    <i class="fa fa-cookie-bite"></i> {{trans('general.cookies_text')}}
    @if($settings->link_cookies != '')
      <a href="{{$settings->link_cookies}}" class="mr-2 text-white link-border" target="_blank">{{ trans('general.cookies_policy') }}</a>
    @endif
    <button class="btn btn-sm btn-success" id="close-banner">{{trans('general.go_it')}}
    </button>
  </div>

  <div id="mobileMenuOverlay" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"></div>

  @auth
    @include('includes.menu-mobile')
  @endauth

  @if ($settings->alert_adult == 'on')
    <div class="modal fade" tabindex="-1" id="alertAdult">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body p-4">
          <p>{{ __('general.alert_content_adult') }}</p>
        </div>
        <div class="modal-footer border-0 pt-0">
          <a href="https://google.com" class="btn e-none p-0 mr-3">{{trans('general.leave')}}</a>
          <button type="button" class="btn btn-primary" id="btnAlertAdult">{{trans('general.i_am_age')}}</button>
        </div>
      </div>
    </div>
  </div>
  @endif


  <div class="popout popout-error font-default"></div>

@if (Auth::guest() && request()->path() == '/' && $settings->home_style == 0
    || Auth::guest() && request()->path() != '/' && $settings->home_style == 0
    || Auth::guest() && request()->path() != '/' && $settings->home_style == 1
    || Auth::check()
    )
  @include('includes.navbar')
  @endif

  <main role="main">
    @yield('content')

    @if (Auth::guest() && ! request()->route()->named('profile')
          || Auth::check()
          && request()->path() != '/'
          && ! request()->is('my/bookmarks')
          && ! request()->route()->named('profile')
          )

          @if (Auth::guest() && request()->path() == '/' && $settings->home_style == 0 && ! request()->is('offline')
                || Auth::guest() && request()->path() != '/' && $settings->home_style == 0 && ! request()->is('offline')
                || Auth::guest() && request()->path() != '/' && $settings->home_style == 1 && ! request()->is('offline')
                || Auth::check()
                  )
            @include('includes.footer')
          @endif

  @endif

  @guest

  @if (! request()->is('/')
      && ! request()->is('login')
      && ! request()->is('signup')
      && ! request()->is('password/reset')
      && ! request()->is('password/reset/*')
      && ! request()->is('contact')
      )
    <div class="modal fade" id="loginFormModal" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-sm modal-login" role="document">
        <div class="modal-content">
          <div class="modal-body p-0">
              <div class="card-body px-lg-5 py-lg-5 position-relative">

                <h6 class="modal-title text-center mb-3">{{ __('general.login_continue') }}</h6>

                @if ($settings->facebook_login == 'on' || $settings->google_login == 'on' || $settings->twitter_login == 'on')
                <div class="mb-2 w-100">

                  @if ($settings->facebook_login == 'on')
                    <a href="{{url('oauth/facebook')}}" class="btn btn-facebook auth-form-btn flex-grow mb-2 w-100">
                      <i class="fab fa-facebook mr-2"></i> {{ __('auth.login_with') }} Facebook
                    </a>
                  @endif

                  @if ($settings->twitter_login == 'on')
                  <a href="{{url('oauth/twitter')}}" class="btn btn-twitter auth-form-btn mb-2 w-100">
                    <i class="fab fa-twitter mr-2"></i> {{ __('auth.login_with') }} Twitter
                  </a>
                @endif

                    @if ($settings->google_login == 'on')
                    <a href="{{url('oauth/google')}}" class="btn btn-google auth-form-btn flex-grow w-100">
                      <img src="{{ url('public/img/google.svg') }}" class="mr-2" width="18" height="18"> {{ __('auth.login_with') }} Google
                    </a>
                  @endif
                  </div>

                  <small class="btn-block text-center my-3 text-uppercase or">{{__('general.or')}}</small>

                @endif

                <form method="POST" action="{{ route('login') }}" data-url-login="{{ route('login') }}" data-url-register="{{ route('register') }}" id="formLoginRegister" enctype="multipart/form-data">
                    @csrf

                    @if (request()->route()->named('profile'))
                      <input type="hidden" name="isProfile" value="{{ $user->username }}">
                    @endif

                    <input type="hidden" name="isModal" id="isModal" value="true">

                    @if ($settings->captcha == 'on')
                      @captcha
                    @endif

                    <div class="form-group mb-3 display-none" id="full_name">
                      <div class="input-group input-group-alternative">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fa fa-user-circle"></i></span>
                        </div>
                        <input class="form-control"  value="{{ old('name')}}" placeholder="{{trans('auth.full_name')}}" name="name" type="text">
                      </div>
                    </div>

                  <div class="form-group mb-3 display-none" id="email">
                    <div class="input-group input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                      </div>
                      <input class="form-control" value="{{ old('email')}}" placeholder="{{trans('auth.email')}}" name="email" type="text">
                    </div>
                  </div>

                  <div class="form-group mb-3" id="username_email">
                    <div class="input-group input-group-alternative">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                      </div>
                      <input class="form-control" value="{{ old('username_email') }}" placeholder="{{ trans('auth.username_or_email') }}" name="username_email" type="text">

                    </div>
                  </div>
                  <div class="form-group">
                    <div class="input-group input-group-alternative" id="showHidePassword">
                      <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                      </div>
                      <input name="password" type="password" class="form-control" placeholder="{{ trans('auth.password') }}">
                      <div class="input-group-append">
                        <span class="input-group-text c-pointer"><i class="fa fa-eye-slash"></i></span>
                    </div>
                  </div>
                  <small class="form-text text-muted">
                    <a href="{{url('password/reset')}}" id="forgotPassword">
                      {{trans('auth.forgot_password')}}
                    </a>
                  </small>
                  </div>

                  <div class="custom-control custom-control-alternative custom-checkbox" id="remember">
                    <input class="custom-control-input" id=" customCheckLogin" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="custom-control-label" for=" customCheckLogin">
                      <span>{{trans('auth.remember_me')}}</span>
                    </label>
                  </div>

                  <div class="custom-control custom-control-alternative custom-checkbox display-none" id="agree_gdpr">
                    <input class="custom-control-input" id="customCheckRegister" type="checkbox" name="agree_gdpr">
                      <label class="custom-control-label" for="customCheckRegister">
                        <span>{{trans('admin.i_agree_gdpr')}}
                          <a href="{{$settings->link_privacy}}" target="_blank">{{trans('admin.privacy_policy')}}</a>
                        </span>
                      </label>
                  </div>

                  <div class="alert alert-danger display-none mb-0 mt-3" id="errorLogin">
                      <ul class="list-unstyled m-0" id="showErrorsLogin"></ul>
                    </div>

                    <div class="alert alert-success display-none mb-0 mt-3" id="checkAccount"></div>

                  <div class="text-center">
                    <button type="submit" id="btnLoginRegister" class="btn btn-primary mt-4 w-100"><i></i> {{trans('auth.login')}}</button>

                    <div class="w-100 mt-2">
                      <button type="button" class="btn e-none p-0" data-dismiss="modal">{{ __('admin.cancel') }}</button>
                    </div>
                  </div>
                </form>

                @if ($settings->captcha == 'on')
                  <small class="btn-block text-center mt-3">{{trans('auth.protected_recaptcha')}} <a href="https://policies.google.com/privacy" target="_blank">{{trans('general.privacy')}}</a> - <a href="https://policies.google.com/terms" target="_blank">{{trans('general.terms')}}</a></small>
                @endif

                @if ($settings->registration_active == '1')
                <div class="row mt-3">
                  <div class="col-12 text-center">
                    <a href="javascript:void(0);" id="toggleLogin" data-not-account="{{trans('auth.not_have_account')}}" data-already-account="{{trans('auth.already_have_an_account')}}" data-text-login="{{trans('auth.login')}}" data-text-register="{{trans('auth.sign_up')}}">
                      <strong>{{trans('auth.not_have_account')}}</strong>
                    </a>
                  </div>
                </div>
                @endif

              </div><!-- ./ card-body -->
            </div>
          </div>
        </div>
      </div>
    </div><!-- End Modal -->
    @endif
  @endguest

  @auth
    <div class="modal fade" id="tipForm" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white shadow border-0">
              <div class="card-header pb-2 border-0 position-relative" style="height: 100px; background: {{$settings->color_default}} @if (auth()->user()->cover != '')  url('{{Helper::getFile(config('path.cover').auth()->user()->cover)}}') @endif no-repeat center center; background-size: cover;">

              </div>
              <div class="card-body px-lg-5 py-lg-5 position-relative">

                <div class="text-muted text-center mb-3 position-relative modal-offset">
                  <img src="{{Helper::getFile(config('path.avatar').auth()->user()->avatar)}}" width="100" class="avatar-modal rounded-circle mb-1">
                  <h6>
                    {{trans('general.send_tip')}} <span class="userNameTip"></span>
                  </h6>
                </div>

                <form method="post" action="{{url('send/tip')}}" id="formSendTip">

                  <input type="hidden" name="id" class="userIdInput" value="{{auth()->user()->id}}"  />

                  @if (request()->is('messages/*'))
                    <input type="hidden" name="isMessage" value="1"  />
                  @endif

                  <input type="hidden" id="cardholder-name" value="{{ auth()->user()->name }}"  />
                  <input type="hidden" id="cardholder-email" value="{{ auth()->user()->email }}"  />
                  <input type="number" min="{{$settings->min_donation_amount}}"  autocomplete="off" id="onlyNumber" class="form-control mb-3" name="amount" placeholder="{{trans('general.tip_amount')}}">

                  @csrf

                  @foreach (PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get() as $payment)

                    @php

                    if ($payment->type == 'card' ) {
                      $paymentName = '<i class="far fa-credit-card mr-1"></i> '.trans('general.debit_credit_card') .' <small class="w-100 d-block">'.__('general.powered_by').' '.$payment->name.'</small>';
                    } else if ($payment->id == 1) {
                      $paymentName = '<img src="'.url('public/img/payments', auth()->user()->dark_mode == 'off' ? $payment->logo : 'paypal-white.png').'" width="70"/> <small class="w-100 d-block">'.trans('general.redirected_to_paypal_website').'</small>';
                    } else {
                      $paymentName = '<img src="'.url('public/img/payments', $payment->logo).'" width="70"/>';
                    }

                    $allPayments = PaymentGateways::where('enabled', '1')->whereSubscription('yes')->get();

                    @endphp
                    <div class="custom-control custom-radio mb-3">
                      <input name="payment_gateway_tip" value="{{$payment->id}}" id="tip_radio{{$payment->id}}" @if ($allPayments->count() == 1 && auth()->user()->wallet == 0.00) checked @endif class="custom-control-input" type="radio">
                      <label class="custom-control-label" for="tip_radio{{$payment->id}}">
                        <span><strong>{!!$paymentName!!}</strong></span>
                      </label>
                    </div>

                    @if ($payment->name == 'Stripe')
                    <div id="stripeContainerTip" class="@if ($allPayments->count() != 1) display-none @endif">
                      <div id="card-element" class="margin-bottom-10">
                        <!-- A Stripe Element will be inserted here. -->
                      </div>
                      <!-- Used to display form errors. -->
                      <div id="card-errors" class="alert alert-danger display-none" role="alert"></div>
                    </div>
                    @endif

                    @if ($payment->name == 'Paystack' && ! auth()->user()->paystack_authorization_code != '')
                      <div id="paystackContainerTip" class="@if ($allPayments->count() != 1) display-none @endif">
                      <a href="{{ url('my/cards') }}" class="btn btn-secondary btn-sm mb-3 w-100">
                        <i class="far fa-credit-card mr-2"></i>
                        {{ trans('general.add_payment_card') }}
                      </a>
                      </div>
                    @endif

                  @endforeach

                  <div class="custom-control custom-radio mb-3">
                    <input name="payment_gateway_tip" @if (Auth::user()->wallet == 0) disabled @endif value="wallet" id="tip_radio0" class="custom-control-input" type="radio">
                    <label class="custom-control-label" for="tip_radio0">
                      <span>
                        <strong>
                        <i class="fas fa-wallet mr-1"></i> {{ __('general.wallet') }}
                        <span class="w-100 d-block font-weight-light">
                          {{ __('general.available_balance') }}: <span class="font-weight-bold mr-1 balanceWallet">{{Helper::amountFormatDecimal(Auth::user()->wallet)}}</span>

                          @if (Auth::user()->wallet == 0)
                          <a href="{{ url('my/wallet') }}" class="link-border">{{ __('general.recharge') }}</a>
                        @endif
                        </span>
                      </strong>
                      </span>
                    </label>
                  </div>

                  <div class="alert alert-danger display-none" id="errorTip">
                      <ul class="list-unstyled m-0" id="showErrorsTip"></ul>
                    </div>

                  <div class="text-center">
                    <button type="button" class="btn e-none mt-4" data-dismiss="modal">{{trans('admin.cancel')}}</button>
                    <button type="submit" id="tipBtn" class="btn btn-primary mt-4 tipBtn"><i></i> {{trans('general.pay')}}</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- End Modal Tip -->
  @endauth
  </main>

  @include('includes.javascript_general')

  @yield('javascript')

@auth
  <div id="bodyContainer"></div>
@endauth
</body>
</html>
