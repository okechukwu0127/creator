<?php

namespace App\Http\Controllers\Traits;

use DB;
use App\Helper;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Subscriptions;
use App\Models\Notifications;
use App\Models\Comments;
use App\Models\Like;
use App\Models\Updates;
use App\Models\Reports;
use App\Models\VerificationRequests;
use App\Models\PaymentGateways;
use App\Models\Conversations;
use App\Models\Messages;
use App\Models\Bookmarks;
use App\Models\Transactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


trait Functions {

	public function __construct(AdminSettings $settings) {
    $this->settings = $settings::first();
  }

	// Users on Card Explore
	public function userExplore()
	{
		return User::where('status','active')
			->where('id', '<>', Auth::user()->id ?? 0)
				->whereVerifiedId('yes')
				->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
				->where('price', '<>', 0.00)
				->whereFreeSubscription('no')
				->whereHideProfile('no')
			->orWhere('status','active')
				->where('id', '<>', Auth::user()->id ?? 0)
					->whereVerifiedId('yes')
					->where('id', '<>', $this->settings->hide_admin_profile == 'on' ? 1 : 0)
					->whereFreeSubscription('yes')
					->whereHideProfile('no')
				->inRandomOrder()
				->paginate(3);
	}// End Method

	// CCBill Form
	public function ccbillForm($price, $userAuth, $type, $creator = null, $isMessage = null)
	{
		// Get Payment Gateway
		$payment = PaymentGateways::whereName('CCBill')->firstOrFail();

		if ($creator) {
		$user  = User::whereVerifiedId('yes')->whereId($creator)->firstOrFail();
		}

		$currencyCodes = [
			'AUD' => 036,
			'CAD' => 124,
			'JPY' => 392,
			'GBP' => 826,
			'USD' => 840,
			'EUR' => 978
		];

		if ($type == 'wallet') {
			if ($this->settings->currency_code == 'JPY') {
				$formPrice = round($price + ($price * $payment->fee / 100) + $payment->fee_cents, 2, '.', ',');
			} else {
				$formPrice = number_format($price + ($price * $payment->fee / 100) + $payment->fee_cents, 2, '.', ',');
			}
		} else {
			$formPrice = number_format($price, 2);
		}

		$formInitialPeriod = 365;
		$currencyCode = array_key_exists($this->settings->currency_code, $currencyCodes) ? $currencyCodes[$this->settings->currency_code] : 840;

		// Hash
		$hash = md5($formPrice . $formInitialPeriod . $currencyCode . $payment->ccbill_salt);

		$input['clientAccnum']  = $payment->ccbill_accnum;
		$input['clientSubacc']  = $payment->ccbill_subacc;
		$input['currencyCode']  = $currencyCode;
		$input['formDigest']    = $hash;
		$input['initialPrice']  = $formPrice;
		$input['initialPeriod'] = $formInitialPeriod;
		$input['type']          = $type;
		$input['isMessage']     = $isMessage;
		$input['creator']       = $user->id ?? null;
		$input['user']          = $userAuth;
		$input['amountFixed']   = $type == 'wallet' ? $price : null;

		// Base url
		$baseURL = 'https://api.ccbill.com/wap-frontflex/flexforms/' . $payment->ccbill_flexid;

		// Build redirect url
		$inputs = http_build_query($input);
		$redirectUrl = $baseURL . '?' . $inputs;

		return response()->json([
								'success' => true,
								'url' => $redirectUrl,
						]);

	}// End Method

}// End Class
