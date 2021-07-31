<link href="{{ asset('public/css/core.min.css') }}?v={{$settings->version}}" rel="stylesheet">
<link href="{{ asset('public/css/feather.css') }}" rel="stylesheet">
<link href="{{ asset('public/css/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('public/css/icomoon.css') }}" rel="stylesheet">
@if (Auth::check() && auth()->user()->dark_mode == 'on')
  <link href="{{ asset('public/css/bootstrap-dark.min.css') }}" rel="stylesheet">
@else
  <link href="{{ asset('public/css/bootstrap.min.css') }}" rel="stylesheet">
@endif
<link href="{{ asset('public/css/styles.css') }}?v={{$settings->version}}" rel="stylesheet">
<link href="{{ asset('public/css/smartphoto.min.css') }}" rel="stylesheet">
@if (Auth::check() && request()->path() == '/' || request()->route()->named('profile') || request()->is('messages/*') || request()->is('my/bookmarks'))
<link href="{{ asset('public/js/plyr/plyr.css')}}?v={{$settings->version}}" rel="stylesheet" type="text/css" />
@endif
<script type="text/javascript">
// Global variables
  var URL_BASE = "{{ url('/') }}";
  var _title = '@section("title")@show {{e($settings->title)}}';
  var session_status = "{{ Auth::check() ? 'on' : 'off' }}";
  var totalPosts = @if(isset($updates)) {{ $updates->total() }}@else 0 @endif;
  var ReadMore = "{{trans('general.view_all')}}";
  var copiedSuccess = "{{trans('general.copied_success')}}";
  var copied = "{{trans('general.copied')}}";
  var copy_link = "{{trans('general.copy_link')}}";
  var loading = "{{trans('general.loading')}}";
  var please_wait = "{{trans('general.please_wait')}}";
  var error_occurred = "{{trans('general.error')}}";
  var error_oops = "{{ trans('general.error_oops') }}";
  var error_reload_page = "{{ trans('general.error_reload_page') }}";
  var ok = "{{trans('users.ok')}}";
  var user_count_carousel = @if (Auth::guest() && request()->path() == '/') {{$users->count()}}@else 0 @endif;
  var no_results_found = "{{trans('general.no_results_found')}}";
  var no_results = "{{trans('general.no_results')}}";
  var is_profile = {{ request()->route()->named('profile') ? 'true' : 'false' }};
  var error_scrollelement = false;
  var captcha = {{ $settings->captcha == 'on' ? 'true' : 'false' }};
  var alert_adult = {{ $settings->alert_adult == 'on' ? 'true' : 'false' }};
@auth
  var is_bookmarks = {{ request()->is('my/bookmarks') ? 'true' : 'false' }};
  var delete_confirm = "{{trans('general.delete_confirm')}}";
  var confirm_delete_comment = "{{trans('general.confirm_delete_comment')}}";
  var confirm_delete_update = "{{trans('general.confirm_delete_update')}}";
  var yes_confirm = "{{trans('general.yes_confirm')}}";
  var cancel_confirm = "{{trans('general.cancel_confirm')}}";
  var formats_available = "{{trans('general.formats_available')}}";
  var formats_available_images = "{{trans('general.formats_available_images')}}";
  var formats_available_verification = "{{trans('general.formats_available_verification')}}";
  var file_size_allowed = {{$settings->file_size_allowed * 1024}};
  var max_size_id = "{{trans('general.max_size_id').' '.Helper::formatBytes($settings->file_size_allowed * 1024)}}";
  var max_size_id_lang = "{{trans('general.max_size_id').' '.Helper::formatBytes($settings->file_size_allowed_verify_account * 1024)}}";
  var file_size_allowed_verify_account = {{$settings->file_size_allowed_verify_account * 1024}};
  var error_width_min = "{{trans('general.width_min',['data' => 20])}}";
  var story_length = {{$settings->story_length}};
  var payment_card_error = "{{ trans('general.payment_card_error') }}";
  var confirm_delete_message = "{{trans('general.confirm_delete_message')}}";
  var confirm_delete_conversation = "{{trans('general.confirm_delete_conversation')}}";
  var confirm_cancel_subscription = "{!!trans('general.confirm_cancel_subscription')!!}";
  var yes_confirm_cancel = "{{trans('general.yes_confirm_cancel')}}";
  var confirm_delete_notifications = "{{trans('general.confirm_delete_notifications')}}";
  var confirm_delete_withdrawal = "{{trans('general.confirm_delete_withdrawal')}}";
  var change_cover = "{{trans('general.change_cover')}}";
  var pin_to_your_profile = "{{trans('general.pin_to_your_profile')}}";
  var unpin_from_profile = "{{trans('general.unpin_from_profile')}}";
  var post_pinned_success = "{{trans('general.post_pinned_success')}}";
  var post_unpinned_success = "{{trans('general.post_unpinned_success')}}";
  var stripeKey = "{{ PaymentGateways::where('id', 2)->where('enabled', '1')->first() ? env('STRIPE_KEY') : false }}";
  var thanks = "{{ trans('general.thanks') }}";
  var tip_sent_success = "{{ trans('general.tip_sent_success') }}";
  var error_payment_stripe_3d = "{{ trans('general.error_payment_stripe_3d') }}";
  var colorStripe = {!! auth()->user()->dark_mode == 'on' ? "'#dcdcdc'" : "'#32325d'" !!};
  var full_name_user = '{{ auth()->user()->name }}';
  var color_default = '{{ $settings->color_default }}';
  var formats_available_upload_file = "{{trans('general.formats_available_upload_file')}}";
  var cancel_subscription = "{{trans('general.unsubscribe')}}";
  var your_subscribed = "{{trans('general.your_subscribed')}}";
  var subscription_expire = "{{trans('general.subscription_expire')}}";
  var formats_available_verification_form_w9 = "{{trans('general.formats_available_verification_form_w9', ['formats' => 'PDF'])}}";
@endauth
</script>

<style type="text/css">

@if ($settings->custom_css)
  {!! $settings->custom_css !!}
@endif

@if (auth()->check() && auth()->user()->dark_mode == 'on' )
  body { color: #FFF; }
  .dd-menu-user:before { color: #222222; }
  .dropdown-item.balance:hover {background: #222 !important;color: #ffffff;}
  .blocked {background-color: transparent;}
  .btn-google, .btn-google:hover, .btn-google:active, .btn-google:focus {
  background: transparent;
  border-color: #ccc;
  color: #fff;
}
.img-user,
.avatar-modal,
.img-user-small { border-color: #303030; }
.actionDeleteNotify,
.actionDeleteNotify:hover { color: #FFF; }

.nav-profile a, .nav-profile li.active a:hover, .nav-profile li.active a:active, .nav-profile li.active a:focus,
.sm-btn-size, .verified {
  color: #fff;
}
.text-featured {color: #fff !important;}
.input-group-text {
  border-color: #303030;
  background-color: #303030;
}
.form-control:focus, .custom-select:focus {
  border-color: #222 !important;
}
.custom-select {
  background: #303030 url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23a5a5a5' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e")
  no-repeat right .75rem center/8px 10px;
  color: #fff;
}
.navbar-toggler,
.sweet-alert h2,
.sweet-alert p,
.ico-no-result {
  color: #FFF;
}
.sweet-alert { background-color: #2f2f2f;}
.content-locked {background: #444444;}

@media (max-width: 991px) {
.navbar .navbar-collapse {
  background: #222;
}
.navbar .navbar-collapse .navbar-nav .nav-item .nav-link:not(.btn) {
  color: #ffffff;
}

.navbar-collapse .navbar-toggler span {
  background: #fff;
}
}
.link-scroll a.nav-link:not(.btn) {
  color: #969696;
}
.btn-upload:hover {
background-color: #222222;
}
.modal-danger .modal-content {
background-color: #303030;
}
h3, .h3 {font-size: 1.75rem;}
h2, .h2 {font-size: 2rem;}
h4, .h4 {font-size: 1.5rem;}
h5, .h5 {font-size: 1.25rem;}

@keyframes animate {
from {transition:none;}
to {background-color:#383838;transition: all 0.3s ease-out;}
}

.item-loading::before {
  background-color: #6b6b6b;
  content: ' ';
  display: block;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  animation-name: animate;
  animation-duration: 2s;
  animation-iteration-count: infinite;
  animation-timing-function: linear;
  background-image: none;
  border-radius: 0;
}
.loading-avatar::before {
border-radius: 50%;
}
.loading-avatar {background-color: inherit;}
.plyr--audio .plyr__controls {background: #212121; color: #ffffff;}
.readmore-js-collapsed:after {background-image: linear-gradient(hsla(0,0%,100%,0),#303030 95%);}
.sweet-alert .sa-icon.sa-success .sa-fix {background-color: #2f2f2f;}
.sweet-alert .sa-icon.sa-success::after, .sweet-alert .sa-icon.sa-success::before {background: #2f2f2f;}
.page-item.disabled .page-link, .page-link {background-color: #222222;}
.nav-pills .nav-link {background-color: #303030; color: #ffffff;}
a.social-share i {color: #dedede!important;}

.StripeElement {background-color: #222222; border: 1px solid #222222;}
.StripeElement--focus {border-color: #525252;}
.bg-autocomplete {background-color: #222;}

@endif

.bg-gradient {
  background: url('{{url('public/img', $settings->bg_gradient)}}');
  background-size: cover;
}

a.social-share i {color: #797979; font-size: 32px;}
a:hover.social-share { text-decoration: none; }
.btn-whatsapp {color: #50b154 !important;}
.close-inherit {color: inherit !important;}
.btn-twitter { background-color: #1da1f2;  color:#fff !important;}

@media (max-width: 991px) {
  .navbar-user-mobile {
    font-size: 20px;
  }
}

.or {
display:flex;
justify-content:center;
align-items: center;
color:grey;
}

.or:after,
.or:before {
  content: "";
  display: block;
  background: #adb5bd;
  width: 50%;
  height:1px;
  margin: 0 10px;
}

.icon-navbar { font-size: 23px; vertical-align: bottom; @if (auth()->check() && auth()->user()->dark_mode == 'on') color: #FFF !important; @endif }

{{ $settings->button_style == 'rounded' ? '.btn {border-radius: 50rem!important;}' : null }}

@if (auth()->check() && auth()->user()->dark_mode == 'off' || auth()->guest())
.navbar_background_color { background-color: {{ $settings->navbar_background_color }} !important; }
.link-scroll a.nav-link:not(.btn), .navbar-toggler:not(.text-white) { color: {{ $settings->navbar_text_color }} !important; }

@media (max-width: 991px) {
  .navbar .navbar-collapse, .dd-menu-user, .dropdown-item.balance:hover { background-color: {{ $settings->navbar_background_color }} !important; color: {{ $settings->navbar_text_color }} !important; }
  .dd-menu-user a, .dropdown-item:not(.dropdown-lang) { color: {{ $settings->navbar_text_color }} }
  .navbar-collapse .navbar-toggler span { background-color: {{ $settings->navbar_text_color }} !important; }
  .dropdown-divider { border-top-color: {{ $settings->navbar_background_color }} !important;}
  }

.footer_background_color { background-color: {{ $settings->footer_background_color }} !important; }
.footer_text_color, .link-footer:not(.footer-tiny) { color: {{ $settings->footer_text_color }}; }
@endif

@if ($settings->color_default <> '')

:root {
  --plyr-color-main: {{$settings->color_default}};
}

@media (min-width: 767px) {
  .login-btn { padding-top: 12px !important;}
}

::selection{ background-color: {{$settings->color_default}}; color: white; }
::moz-selection{ background-color: {{$settings->color_default}}; color: white; }
::webkit-selection{ background-color: {{$settings->color_default}}; color: white; }

body a,
a:hover,
a:focus,
a.page-link,
.btn-outline-primary {
    color: {{$settings->color_default}};
}
.text-primary {
    color: {{$settings->color_default}}!important;
}

a.text-primary.btnBookmark:hover, a.text-primary.btnBookmark:focus {
  color: {{$settings->color_default}}!important;
}

.btn-primary:not(:disabled):not(.disabled).active,
.btn-primary:not(:disabled):not(.disabled):active,
.show>.btn-primary.dropdown-toggle,
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active,
.btn-primary,
.btn-primary.disabled,
.btn-primary:disabled,
.custom-checkbox .custom-control-input:checked ~ .custom-control-label::before,
.page-item.active .page-link,
.page-link:hover,
.owl-theme .owl-dots .owl-dot span,
.owl-theme .owl-dots .owl-dot.active span,
.owl-theme .owl-dots .owl-dot:hover span
 {
    background-color: {{$settings->color_default}};
    border-color: {{$settings->color_default}};
}
.bg-primary,
.dropdown-item:focus,
.dropdown-item:hover,
.dropdown-item.active,
.dropdown-item:active,
.tooltip-inner,
.custom-range::-webkit-slider-thumb,
.custom-range::-webkit-slider-thumb:active {
    background-color: {{$settings->color_default}}!important;
}

.custom-range::-moz-range-thumb:active,
.custom-range::-ms-thumb:active {
  background-color: {{$settings->color_default}}!important;
}

.custom-checkbox .custom-control-input:indeterminate ~ .custom-control-label::before,
.custom-control-input:focus:not(:checked) ~ .custom-control-label::before,
.btn-outline-primary {
  border-color: {{$settings->color_default}};
}
.custom-control-input:not(:disabled):active~.custom-control-label::before,
.custom-control-input:checked~.custom-control-label::before,
.btn-outline-primary:hover,
.btn-outline-primary:focus,
.btn-outline-primary:not(:disabled):not(.disabled):active,
.list-group-item.active {
    color: #fff;
    background-color: {{$settings->color_default}};
    border-color: {{$settings->color_default}};
}
.popover .arrow::before { border-top-color: rgba(0,0,0,.35) !important; }
.bs-tooltip-bottom .arrow::before {
  border-bottom-color: {{$settings->color_default}}!important;
}
.arrow::before {
  border-top-color: {{$settings->color_default}}!important;
}
.nav-profile li.active {
  border-bottom: 3px solid {{$settings->color_default}}!important;
}
.button-avatar-upload {left: 0;}
input[type='file'] {overflow: hidden;}
.badge-free { top: 10px; right: 10px; background: rgb(0 0 0 / 65%); color: #fff; font-size: 12px;}

.btn-facebook, .btn-twitter, .btn-google {position: relative;}
.btn-facebook i, .btn-twitter i  {
  position: absolute;
    left: 10px;
    bottom: 14px;
    width: 36px;
}

.btn-google img  {
  position: absolute;
    left: 18px;
    bottom: 12px;
    width: 18px;
}

.button-search {top: 0;}

@media (min-width: 768px) {
  .pace {display:none !important;}
}

@media (min-width: 992px) {
  .menuMobile {display:none !important;}
}

.pace{-webkit-pointer-events:none;pointer-events:none;-webkit-user-select:none;-moz-user-select:none;user-select:none}
.pace-inactive{display:none}
.pace .pace-progress{background:{{$settings->color_default}};position:fixed;z-index:2000;top:0;right:100%;width:100%;height:3px}

.menuMobile {
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 1040;
  @if (auth()->check() && auth()->user()->dark_mode == 'off')
    background-color: {{ $settings->navbar_background_color }} !important;
  @endif

}
.btn-mobile {border-radius: 25px;
  @if (auth()->check() && auth()->user()->dark_mode == 'off')
  color: {{$settings->navbar_text_color}} !important;
  @endif
}
.btn-mobile:hover {
    background-color: rgb(243 243 243 / 26%);
    text-decoration: none !important;
    -webkit-transition: all 200ms linear;
    -moz-transition: all 200ms linear;
    -o-transition: all 200ms linear;
    -ms-transition: all 200ms linear;
    transition: all 200ms linear;
}

@media (max-width: 991px) {
  .navbar .navbar-collapse {
    width: 300px !important;
    box-shadow: 5px 0px 8px #000;
  }

  .notify {
    top: 5px !important;
    right: 5px !important;
  }

  @auth
  .margin-auto {
      margin: auto!important;
  }
  @endauth
}
.sidebar-overlay #mobileMenuOverlay {
    position: fixed;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 101;
    -webkit-transition: all .9s;
    -moz-transition: all .8s;
    -ms-transition: all .8s;
    -o-transition: all .8s;
    transition: all .8s;
    transition-delay: .35s;
    left: 0;
}
.noti_notifications, .noti_msg {display: none;}

.link-menu-mobile {border-radius: 6px;}
.link-menu-mobile:hover:not(.balance) {
  background: rgb(242 242 242 / 40%);
}
a.link-border {text-decoration: none;}
@media (max-width: 479px) {
  .card-updates {border-right: 0; border-left: 0;}
  .wrap-post {padding: 0 !important;}
}

@media (min-width: 576px) {
  .modal-login {
      max-width: 415px;
  }
}

@endif
</style>
