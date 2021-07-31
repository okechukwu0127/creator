<div class="col-md-6 col-lg-3 mb-3">

<button type="button" class="btn btn-primary btn-block mb-2 d-lg-none" type="button" data-toggle="collapse" data-target="#navbarUserHome" aria-controls="navbarCollapse" aria-expanded="false">
		<i class="fa fa-bars myicon-right"></i> {{trans('general.menu')}}
	</button>

	<div class="navbar-collapse collapse d-lg-block" id="navbarUserHome">
	<div class="card shadow-sm">
			<div class="list-group list-group-sm list-group-flush">

					<a href="{{url(Auth::user()->username)}}" class="list-group-item list-group-item-action d-flex justify-content-between url-user">
							<div>
									<i class="far fa-user-circle mr-2"></i>
									<span>{{ auth()->user()->verified_id == 'yes' ? trans('general.my_page') : trans('users.my_profile') }}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					@if (auth()->user()->verified_id == 'yes')
					<a href="{{url('dashboard')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('dashboard')) active @endif">
							<div>
									<i class="bi bi-speedometer2 mr-2"></i>
									<span>{{trans('admin.dashboard')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
				@endif
					<a href="{{url('settings/page')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/page')) active @endif">
							<div>
									<i class="bi bi-pencil mr-2"></i>
									<span>{{ auth()->user()->verified_id == 'yes' ? trans('general.edit_my_page') : trans('users.edit_profile')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					<a href="{{url('privacy/security')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('privacy/security')) active @endif">
							<div>
									<i class="bi bi-shield-check mr-2"></i>
									<span>{{trans('general.privacy_security')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					@if (auth()->user()->verified_id == 'yes')
					<a href="{{url('settings/subscription')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/subscription')) active @endif">
							<div>
									<i class="feather icon-refresh-cw mr-2"></i>
									<span>{{trans('general.subscription')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
				@endif
					<a href="{{url('my/wallet')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('my/wallet')) active @endif">
							<div>
									<i class="iconmoon icon-Wallet mr-2"></i>
									<span>{{trans('general.wallet')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>

				@if (Helper::showSectionMyCards())
					<a href="{{url('my/cards')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('my/cards')) active @endif">
							<div>
									<i class="feather icon-credit-card mr-2"></i>
									<span>{{trans('general.my_cards')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					@endif
					<a href="{{url('settings/verify/account')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/verify/account')) active @endif">
							<div>
									<i class="feather icon-check-circle mr-2"></i>
									<span>{{trans('general.verify_account')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					<a href="{{url('settings/password')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/password')) active @endif">
							<div>
									<i class="iconmoon icon-Key mr-2"></i>
									<span>{{trans('auth.password')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					@if (auth()->user()->verified_id == 'yes')
					<a href="{{url('my/subscribers')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('my/subscribers')) active @endif">
							<div>
									<i class="feather icon-users mr-2"></i>
									<span>{{trans('users.my_subscribers')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
				@endif

					<a href="{{url('my/subscriptions')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('my/subscriptions')) active @endif">
							<div>
									<i class="feather icon-user-check mr-2"></i>
									<span>{{trans('users.my_subscriptions')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>

					<a href="{{url('my/payments')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('my/payments') || request()->is('my/payments/received')) active @endif">
							<div>
									<i class="bi bi-receipt mr-2"></i>
									<span>{{trans('general.payments')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
					@if (auth()->user()->verified_id == 'yes')
					<a href="{{url('settings/payout/method')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/payout/method')) active @endif">
							<div>
									<i class="bi bi-credit-card mr-2"></i>
									<span>{{trans('users.payout_method')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>

					<a href="{{url('settings/withdrawals')}}" class="list-group-item list-group-item-action d-flex justify-content-between @if(request()->is('settings/withdrawals')) active @endif">
							<div>
									<i class="bi bi-arrow-left-right mr-2"></i>
									<span>{{trans('general.withdrawals')}}</span>
							</div>
							<div>
									<i class="fas fa-angle-right"></i>
							</div>
					</a>
				@endif
			</div>
	</div>
</div><!-- End Card -->
</div><!-- navbarUserHome -->
