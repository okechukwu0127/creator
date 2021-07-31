<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\AdminSettings;
use App\Models\Subscriptions;
use App\Models\Notifications;
use App\Models\User;
use Fahim\PaypalIPN\PaypalIPNListener;
use App\Helper;
use Mail;
use Carbon\Carbon;
use App\Models\PaymentGateways;
use App\Models\Transactions;

class PayPalController extends Controller
{
  public function __construct(AdminSettings $settings, Request $request) {
		$this->settings = $settings::first();
		$this->request = $request;
	}

  /**
   * Show/Send form PayPal
   *
   * @return response
   */
    public function show()
    {

    if (! $this->request->expectsJson()) {
        abort(404);
    }

    // Find the User
    $user = User::whereVerifiedId('yes')->whereId($this->request->id)->where('id', '<>', Auth::user()->id)->firstOrFail();

      // Get Payment Gateway
      $payment = PaymentGateways::findOrFail($this->request->payment_gateway);

      // Verify environment Sandbox or Live
      if ($payment->sandbox == 'true') {
				$action = "https://www.sandbox.paypal.com/cgi-bin/webscr";
				} else {
				$action = "https://www.paypal.com/cgi-bin/webscr";
				}

        $urlSuccess = url('buy/subscription/success', $user->username).'?paypal=1';
  			$urlCancel   = url('buy/subscription/cancel', $user->username);
  			$urlPaypalIPN = url('paypal/ipn');

  			return response()->json([
  					        'success' => true,
  					        'insertBody' => '<form id="form_pp" name="_xclick" action="'.$action.'" method="post"  style="display:none">
                    <input type="hidden" name="cmd" value="_xclick-subscriptions"/>
                    <input type="hidden" name="return" value="'.$urlSuccess.'">
  					        <input type="hidden" name="cancel_return"   value="'.$urlCancel.'">
              			<input type="hidden" name="notify_url" value="'.$urlPaypalIPN.'">
                    <input type="hidden" name="currency_code" value="'.$this->settings->currency_code.'">
              			<input type="hidden" name="item_name" value="'.trans('general.subscription_desc_buy').' @'.$user->username.'">
                    <input type="hidden" name="custom" value="id='.$this->request->id.'&amount='.$user->price.'&subscriber='.Auth::user()->id.'&name='.Auth::user()->name.'&plan='.$user->plan.'">
              			<input type="hidden" name="a3" value="'.$user->price.'"/>
              			<input type="hidden" name="p3" value="1"/>
              			<input type="hidden" name="t3" value="M"/>
              			<input type="hidden" name="src" value="1"/>
              			<input type="hidden" name="rm" value="2"/>
                    <input type="hidden" name="business" value="'.$payment->email.'">
              			</form> <script type="text/javascript">document._xclick.submit();</script>',
  					    ]);
    }

    /**
     * PayPal IPN
     *
     * @return void
     */
    public function paypalIpn(Request $request) {

      $ipn = new PaypalIPNListener();

			$ipn->use_curl = false;

      $payment = PaymentGateways::find(1);

			if ($payment->sandbox == 'true') {
				// SandBox
				$ipn->use_sandbox = true;
				} else {
				// Real environment
				$ipn->use_sandbox = false;
				}

	    $verified = $ipn->processIpn();

			$custom  = $request->custom;
			parse_str($custom, $data);

			$payment_status = $request->payment_status;
      $txn_type       = $request->txn_type;
      $subscr_id      = $request->subscr_id;

      //========== Processor Fees
      $processorFees = $data['amount'] - (  $data['amount'] * $payment->fee/100 ) - $payment->fee_cents;

      // Earnings Net User
      $earningNetUser = number_format($processorFees - (  $processorFees * $this->settings->fee_commission/100  ), 2);

      // Earnings Net Admin
      $earningNetAdmin = number_format($processorFees - $earningNetUser, 2);

	    if ($verified) {

  switch ($txn_type) {

    case 'subscr_payment':

				if ($payment_status == 'Completed') {

      // Check outh POST variable and insert in DB
			$verifiedTxnId = Transactions::where('txn_id', $request->txn_id)->first();

			if ( ! isset($verifiedTxnId)) {

        // Subscription
        $subscription = Subscriptions::where('subscription_id', $subscr_id)->first();

        if ( ! isset($subscription)) {
          // Insert DB
          $subscription          = new Subscriptions;
          $subscription->user_id = $data['subscriber'];
          $subscription->stripe_plan = $data['plan'];
          $subscription->subscription_id = $subscr_id;
          $subscription->ends_at = now()->addMonths(1);
          $subscription->save();
        }

        // Insert Transaction
        $txn = new Transactions;
        $txn->txn_id  = $request->txn_id;
        $txn->user_id = $data['subscriber'];
        $txn->subscriptions_id = $subscription->id;
        $txn->subscribed = $data['id'];
        $txn->amount   = $data['amount'];
        $txn->earning_net_user  =  $earningNetUser;
        $txn->earning_net_admin = $earningNetAdmin;
        $txn->payment_gateway = 'PayPal';
        $txn->approved = '1';
        $txn->save();

        //Add Earnings to User
        User::find($data['id'])->increment('balance', $earningNetUser);

        // Send Notification to User --- destination, author, type, target
        Notifications::send($data['id'], $data['subscriber'], '1', $data['id']);

			}// <--- Verified Txn ID
    } // <-- Payment status

    break;

    case 'subscr_cancel':

    // Subscription
    $subscription = Subscriptions::where('subscription_id', $subscr_id)->first();
    $subscription->cancelled = 'yes';
    $subscription->save();

    break;

  }// switch
  }// Verified

    }//<----- End Method paypalIpn()
}
