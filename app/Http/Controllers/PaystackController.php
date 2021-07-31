<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminSettings;
use App\Models\PaymentGateways;
use Yabacon\Paystack;
use Yabacon\Paystack\Event;
use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Models\Notifications;
use App\Models\Conversations;
use App\Models\Deposits;
use App\Models\Messages;
use App\Models\User;
use Carbon\Carbon;
use App\Helper;
use Mail;

class PaystackController extends Controller
{
  public function __construct(AdminSettings $settings, Request $request) {
    $this->settings = $settings::first();
    $this->request = $request;
  }

  // Card Authorization
  public function cardAuthorization()
  {
    $pystk = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();

    $paystack = new Paystack($pystk->key_secret);

    try
    {
      $chargeAmount = ['NGN' => '50.00', 'GHS' => '0.10', 'ZAR' => '1', 'USD' => 0.20];

      if (array_key_exists($this->settings->currency_code, $chargeAmount)) {
          $chargeAmount = $chargeAmount[$this->settings->currency_code];
      } else {
          return back()->withErrorMessage(trans('general.error_currency'));
      }

      $tranx = $paystack->transaction->initialize([
        'reusable' => true,
        'email' => auth()->user()->email,
        'amount' => $chargeAmount*100,
        'currency' => $this->settings->currency_code,
        'callback_url' => url('paystack/card/authorization/verify')
      ]);

      // Redirect url
      $urlRedirect = $tranx->data->authorization_url;

      return redirect($urlRedirect);

    } catch(\Exception $e) {
        return back()->withErrorMessage($e->getMessage());
    }
  }

  // Card Authorization Verify
  public function cardAuthorizationVerify()
  {
    $pystk = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();

    if ( ! $this->request->reference) {
      die('No reference supplied');
    }

    // initiate the Library's Paystack Object
    $paystack = new Paystack($pystk->key_secret);
    try
    {
      // verify using the library
      $tranx = $paystack->transaction->verify([
        'reference' => $this->request->reference, // unique to transactions
      ]);
    } catch(\Exception $e){
        die($e->getMessage());
    }

    if ('success' === $tranx->data->status) {

      $user = User::find(Auth::user()->id);
      $user->paystack_authorization_code = $tranx->data->authorization->authorization_code;
      $user->paystack_last4 = $tranx->data->authorization->last4;
      $user->paystack_exp = $tranx->data->authorization->exp_month . '/' .$tranx->data->authorization->exp_year;
      $user->paystack_card_brand = trim($tranx->data->authorization->card_type);
      $user->save();
    }

    return redirect('my/cards')->withSuccessMessage(trans('general.success'));
  }// end method



  /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
    public function show()
    {
      if ( ! $this->request->expectsJson()) {
       
         // abort(404);
      }

      if (auth()->user()->paystack_authorization_code == '') {
        return response()->json([
          "success" => false,
          'errors' => ['error' => trans('general.please_add_payment_card')]
        ]);
      }

      // Find the user to subscribe
      $user = User::whereVerifiedId('yes')->whereId($this->request->id)->where('id', '<>', Auth::user()->id)->firstOrFail();
      $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();

        try {

          // initiate the Library's Paystack Object
          $paystack = new Paystack($payment->key_secret);

          //========== Create Plan if no exists
          if ( ! $user->paystack_plan) {

          $userPlan = $paystack->plan->create([
                  'name'=> trans('general.subscription_for').' @'.$user->username,
                  'amount'=> $user->price*100,
                  'interval'=> 'monthly',
                  'currency'=> $this->settings->currency_code
                ]);

                $planCode = $userPlan->data->plan_code;

                // Insert Plan Code to User
                User::whereId($user->id)->update([
          						'paystack_plan' => $planCode
          					]);
            } else {
              $planCode = $user->paystack_plan;
            }

            //========== Create Subscription
            $subscr = $paystack->subscription->create([
                      'plan'=> $planCode,
                      'customer'=> auth()->user()->email,
                      'authorization'=> auth()->user()->paystack_authorization_code
                    ]);

            $subscription          = new Subscriptions;
            $subscription->user_id = auth()->user()->id;
            $subscription->stripe_plan = 'user_'.$user->id;
            $subscription->subscription_id = $subscr->data->subscription_code;;
            //$subscription->ends_at = now();   
            $subscription->ends_at = Carbon::now()->addMonths(1);
            
            $subscription->save();

            // Send Email to User and Notification
            
            Subscriptions::sendEmailAndNotify(auth()->user()->name, $user->id);

        } catch (\Exception $exception) {
          return response()->json([
            'success' => false,
            'errors' => ['error' => $exception->getMessage()]
          ]);
        }

      return response()->json([
        'success' => true,
        'url' => url('buy/subscription/success', $user->username)
      ]);

    }// end method

    public function cancelSubscription($id)
    {
      $checkSubscription = auth()->user()->userSubscriptions()->whereSubscriptionId($id)->firstOrFail();
      $creator = User::wherePlan($checkSubscription->stripe_plan)->first();
      $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.paystack.co/subscription/".$id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
          "Authorization: Bearer ".$payment->key_secret,
          "Cache-Control: no-cache",
        ),
      ));

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      if ($err) {
        throw new \Exception("cURL Error #:" . $err);
      } else {
         $result = json_decode($response);
      }

      // initiate the Library's Paystack Object
      $paystack = new Paystack($payment->key_secret);

      $paystack->subscription->disable([
                'code'=> $id,
                'token'=> $result->data->email_token
              ]);

    session()->put('subscription_cancel', trans('general.subscription_cancel'));
    return redirect($creator->username);

    }// end method

    // PayStack webhooks
    public function webhooks(Request $request)
    {

      // Get Payment Gateway
      $payment = PaymentGateways::whereName('Paystack')->whereEnabled(1)->firstOrFail();

      // Retrieve the request's body and parse it as JSON
      $event = Event::capture();
      http_response_code(200);

    /* It is a important to log all events received. Add code *
     * here to log the signature and body to db or file       */
      openlog('MyPaystackEvents', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
      syslog(LOG_INFO, $event->raw);
      closelog();

      /* Verify that the signature matches one of your keys*/
      $my_keys = [
                  'live'=> $payment->key_secret,
                  'test'=> $payment->key_secret,
                ];
      $owner = $event->discoverOwner($my_keys);
      if ( ! $owner) {
          // None of the keys matched the event's signature
          die();
      }

    // Do something with $event->obj
    // Give value to your customer but don't give any output
    // Remember that this is a call from Paystack's servers and
    // Your customer is not seeing the response here at all
    switch ($event->obj->event) {

      // subscription.create
      case 'subscription.create':

              // Get all data
              $data = $event->obj->data;

              // Amount
              $amount = $data->amount/100;

              // Subscription ID
              $subscr_id = $data->subscription_code;

              // Update subscription
              $subscription = Subscriptions::where('subscription_id', $subscr_id)->firstOrFail();
              $subscription->ends_at = now()->addMonths(1);
              $subscription->save();

              // User Plan
              $user = User::wherePlan($subscription->stripe_plan)->firstOrFail();

              //========== Processor Fees
              $processorFees = $amount - ($amount * $payment->fee/100) - $payment->fee_cents;

              // Earnings Net User
              $earningNetUser = $processorFees - ($processorFees * $this->settings->fee_commission/100);
              // Earnings Net Admin
              $earningNetAdmin = $processorFees - $earningNetUser;

              $userEarning = number_format($earningNetUser, 2);
              $adminEarning = number_format($earningNetAdmin, 2);

              // Insert Transaction
              $txn = new Transactions;
              $txn->txn_id  = $subscr_id;
              $txn->user_id = $subscription->user_id;
              $txn->subscriptions_id = $subscription->id;
              $txn->subscribed = $user->id;
              $txn->amount   = $amount;
              $txn->earning_net_user  =  $userEarning;
              $txn->earning_net_admin = $adminEarning;
              $txn->payment_gateway = 'Paystack';
              $txn->save();

              // Add Earnings to User
              $user->increment('balance', $userEarning);

          break;

          // subscription.disable
          case 'subscription.disable':

                // Get all data
                $data = $event->obj->data;

                // Subscription ID
                $subscr_id = $data->subscription_code;

                // Update subscription
                $subscription = Subscriptions::where('subscription_id', $subscr_id)->firstOrFail();
                $subscription->cancelled = 'yes';
                $subscription->save();

          break;

          // invoice.create
          case 'invoice.create':

          // Get all data
          $data = $event->obj->data;

          // Amount
          $amount = $data->amount/100;

          // Subscription ID
          $subscr_id = $data->subscription->subscription_code;

          // Transaction reference
          $txn_id = $data->transaction->reference;

          // Update subscription
          $subscription = Subscriptions::where('subscription_id', $subscr_id)->firstOrFail();

          // User Plan
          $user = User::wherePlan($subscription->stripe_plan)->firstOrFail();

          //========== Processor Fees
          $processorFees = $amount - ($amount * $payment->fee/100) - $payment->fee_cents;

          // Earnings Net User
          $earningNetUser = $processorFees - ($processorFees * $this->settings->fee_commission/100);
          // Earnings Net Admin
          $earningNetAdmin = $processorFees - $earningNetUser;

          $userEarning = number_format($earningNetUser, 2);
          $adminEarning = number_format($earningNetAdmin, 2);

          // Insert Transaction
          $txn = new Transactions;
          $txn->txn_id  = $txn_id;
          $txn->user_id = $subscription->user_id;
          $txn->subscriptions_id = $subscription->id;
          $txn->subscribed = $user->id;
          $txn->amount   = $amount;
          $txn->earning_net_user  =  $userEarning;
          $txn->earning_net_admin = $adminEarning;
          $txn->payment_gateway = 'Paystack';
          $txn->approved = '0'; // Pending
          $txn->save();

          break;

        // charge.success
        case 'charge.success':

                // Get all data
                $data = $event->obj->data;

                // Amount
                $amount = $data->amount/100;

                // Metadata
                $metadata = $data->metadata;

                //======== Add Funds
                if (isset($metadata->addFunds)) {

                  // Verify Deposit
      						$verifyTxnId = Deposits::where('txn_id', $data->reference)->first();

                  if ( ! isset($verifyTxnId)) {
                    // Insert DB
                    $sql          = new Deposits;
                    $sql->user_id = $metadata->user;
                    $sql->txn_id  = $data->reference;
                    $sql->amount  = $metadata->amount;
                    $sql->payment_gateway = 'Paystack';
                    $sql->save();

                    // Add Funds to User
                    User::find($metadata->user)->increment('wallet', $metadata->amount);
                  }
                } // End Add Funds

                //======== Tips
                if (isset($metadata->tip)) {

                  // Verify transaction
      						$verifyTxnId = Transactions::where('txn_id', $data->reference)->first();

                if ( ! isset($verifyTxnId)) {

                  // Find creator
                  $creator = User::findOrFail($metadata->creator);

                  //========== Processor Fees
                  $processorFees = $amount - ($amount * $payment->fee/100) - $payment->fee_cents;

                  // Earnings Net User
                  $earningNetUser = $processorFees - ($processorFees * $this->settings->fee_commission/100);
                  // Earnings Net Admin
                  $earningNetAdmin = $processorFees - $earningNetUser;

                  $userEarning = number_format($earningNetUser, 2);
                  $adminEarning = number_format($earningNetAdmin, 2);

                  // Insert Transaction
                  $txn = new Transactions;
                  $txn->txn_id  = $data->reference;
                  $txn->user_id = $metadata->tipper;
                  $txn->subscriptions_id = 0;
                  $txn->subscribed = $creator->id;
                  $txn->amount   = $amount;
                  $txn->earning_net_user  =  $userEarning;
                  $txn->earning_net_admin = $adminEarning;
                  $txn->payment_gateway = 'Paystack';
                  $txn->type = 'tip';
                  $txn->save();

                  // Add Earnings to User
                  $creator->increment('balance', $userEarning);

                  // Send Notification
                  Notifications::send($creator->id, $metadata->tipper, '5', $metadata->tipper);

                  //============== Check if the tip is sent by message
                  if (isset($metadata->isMessage)) {

                    // Verify Conversation Exists
            				$conversation = Conversations::where('user_1', $metadata->tipper)
              				->where('user_2', $creator->id)
              				->orWhere('user_1', $creator->id)
              				->where('user_2', $metadata->tipper)->first();

                      if ( ! isset($conversation)) {
                        $newConversation = new Conversations;
                        $newConversation->user_1 = $metadata->tipper;
                        $newConversation->user_2 = $creator->id;
                        $newConversation->updated_at = now();
                        $newConversation->save();

                        $conversationID = $newConversation->id;

                      } else {
                        $conversation->updated_at = now();
                        $conversation->save();

                        $conversationID = $conversation->id;
                      }

                      $message = new Messages();
                      $message->conversations_id = $conversationID;
                      $message->from_user_id    = $metadata->tipper;
                      $message->to_user_id      = $creator->id;
                      $message->message         = '';
                      $message->updated_at      = now();
                      $message->tip             = 'yes';
                      $message->tip_amount      = $amount;
                      $message->save();

                   } // end isMessage
                  } // end verifyTxnId
                }// Ends Tips

                //======== Renew subscription
                if (isset($data->plan)) {

                  // Transaction reference
                  $txn_id = $data->reference;

                  // Update Transaction
                  $txn = Transactions::whereTxnId($txn_id)->firstOrFail();
                  $txn->approved = '1'; // Success
                  $txn->save();

                  // Update subscription
                  $subscription = Subscriptions::findOrFail($txn->subscriptions_id);
                  $subscription->ends_at = now()->addMonths(1);
                  $subscription->save();

                  // User Plan
                  $user = User::whereId($txn->subscribed)->firstOrFail();

                  // Add Earnings to User
                  $user->increment('balance', $txn->earning_net_user);

                }// End Renew subscription

            break;

          } // switch

    }// end method

}
