<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notifications extends Model
{
	protected $guarded = array();
  const UPDATED_AT = null;

	public function user()
	{
		return $this->belongsTo('App\Models\User')->first();
	}

	public static function send($destination, $session_id, $type, $target)
	{
		$user = User::find($destination);

		if ($type == '5' && $user->notify_new_tip == 'no') {
			return false;
		}

		$noty = new Notifications;

		$noty->destination = $destination;
		$noty->author      = $session_id;
		$noty->type        = $type;
		$noty->target      = $target;
		$noty->save();


	}

}
