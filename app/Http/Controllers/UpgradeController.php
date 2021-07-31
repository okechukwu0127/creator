<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Categories;
use App\Models\User;
use App\Models\AdminSettings;
use App\Models\Subscriptions;
use App\Models\Updates;
use App\Models\PaymentGateways;
use App\Models\Languages;
use App\Helper;

class UpgradeController extends Controller {

	public function __construct(AdminSettings $settings, Updates $updates, User $user) {
		$this->settings  = $settings::first();
		$this->user      = $user::first();
		$this->updates   = $updates::first();
 }

 /**
	* Move a file
	*
	*/
 private static function moveFile($file, $newFile, $copy)
 {
	 if (File::exists($file) && $copy == false) {
		 	 File::delete($newFile);
			 File::move($file, $newFile);
	 } else if(File::exists($newFile) && isset($copy)) {
			 File::copy($newFile, $file);
	 }
 }

 /**
	* Copy a directory
	*
	*/
 private static function moveDirectory($directory, $destination, $copy)
 {
	 if (File::isDirectory($directory) && $copy == false) {
			 File::moveDirectory($directory, $destination);
	 } else if(File::isDirectory($destination) && isset($copy)) {
			 File::copyDirectory($destination, $directory);
	 }
 }

	public function update($version)
	{
		$DS = DIRECTORY_SEPARATOR;

		$ROOT = base_path().$DS;
		$APP = app_path().$DS;
		$BOOTSTRAP_CACHE = base_path('bootstrap'.$DS.'cache').$DS;
		$MODELS = app_path('Models').$DS;
		$CONTROLLERS = app_path('Http'. $DS . 'Controllers').$DS;
		$CONTROLLERS_AUTH = app_path('Http'. $DS . 'Controllers'. $DS . 'Auth').$DS;
		$MIDDLEWARE = app_path('Http'. $DS . 'Middleware'). $DS;
		$TRAITS = app_path('Http'. $DS . 'Controllers'. $DS . 'Traits').$DS;

		$CONFIG = config_path().$DS;
		$ROUTES = base_path('routes').$DS;

		$PUBLIC_JS_ADMIN = public_path('admin'.$DS.'js').$DS;
		$PUBLIC_JS = public_path('js').$DS;
		$PUBLIC_CSS = public_path('css').$DS;
		$PUBLIC_IMG = public_path('img').$DS;
		$PUBLIC_IMG_ICONS = public_path('img'.$DS.'icons').$DS;
		$PUBLIC_FONTS = public_path('webfonts').$DS;

		$VIEWS = resource_path('views').$DS;
		$VIEWS_ADMIN = resource_path('views'. $DS . 'admin').$DS;
		$VIEWS_AJAX = resource_path('views'. $DS . 'ajax').$DS;
		$VIEWS_AUTH = resource_path('views'. $DS . 'auth').$DS;
		$VIEWS_AUTH_PASS = resource_path('views'. $DS . 'auth'.$DS.'passwords').$DS;
		$VIEWS_EMAILS = resource_path('views'. $DS . 'emails').$DS;
		$VIEWS_ERRORS = resource_path('views'. $DS . 'errors').$DS;
		$VIEWS_INCLUDES = resource_path('views'. $DS . 'includes').$DS;
		$VIEWS_INSTALL = resource_path('views'. $DS . 'installer').$DS;
		$VIEWS_INDEX = resource_path('views'. $DS . 'index').$DS;
		$VIEWS_LAYOUTS = resource_path('views'. $DS . 'layouts').$DS;
		$VIEWS_PAGES = resource_path('views'. $DS . 'pages').$DS;
		$VIEWS_USERS = resource_path('views'. $DS . 'users').$DS;

		$upgradeDone = '<h2 style="text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #4BBA0B;">'.trans('admin.upgrade_done').' <a style="text-decoration: none; color: #F50;" href="'.url('/').'">'.trans('error.go_home').'</a></h2>';

		if ($version == '1.1') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			//============== Files Affected ================//
			$file1 = 'Helper.php';
			$file2 = 'UserController.php';
			$file3 = 'StripeWebHookController.php';

			$file4 = 'Messages.php';
			$file5 = 'Comments.php';
			$file6 = 'Notifications.php';

			$file7 = 'edit_my_page.blade.php';
			$file8 = 'blog.blade.php';
			$file9 = 'posts.blade.php';
			$file10 = 'updates.blade.php';

			$file11 = 'app-functions.js';


			//============== Moving Files ================//
			$this->moveFile($path.$file1, $APP.$file1, $copy);
			$this->moveFile($path.$file2, $CONTROLLERS.$file2, $copy);
			$this->moveFile($path.$file3, $CONTROLLERS.$file3, $copy);

			$this->moveFile($path.$file4, $MODELS.$file4, $copy);
			$this->moveFile($path.$file5, $MODELS.$file5, $copy);
			$this->moveFile($path.$file6, $MODELS.$file6, $copy);

			$this->moveFile($path.$file7, $VIEWS_USERS.$file7, $copy);
			$this->moveFile($path.$file8, $VIEWS_INDEX.$file8, $copy);
			$this->moveFile($path.$file9, $VIEWS_ADMIN.$file9, $copy);
			$this->moveFile($path.$file10, $VIEWS_INCLUDES.$file10, $copy);

			$this->moveFile($path.$file11, $PUBLIC_JS.$file11, $copy);


			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.1 ----->>

		if ($version == '1.2') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			if (! Schema::hasColumn('admin_settings', 'widget_creators_featured', 'home_style')) {
						Schema::table('admin_settings', function($table) {
						 $table->enum('widget_creators_featured', ['on', 'off'])->default('on');
						 $table->unsignedInteger('home_style');
				});
			}

			if (! Schema::hasColumn('updates', 'fixed_post')) {
						Schema::table('updates', function($table) {
						 $table->enum('fixed_post', ['0', '1'])->default('0');
				});
			}

			if (! Schema::hasColumn('users', 'dark_mode')) {
						Schema::table('users', function($table) {
						 $table->enum('dark_mode', ['on', 'off'])->default('off');
				});
			}

			// Create Table Bookmarks
				if ( ! Schema::hasTable('bookmarks')) {
					Schema::create('bookmarks', function($table)
							 {
									 $table->increments('id');
									 $table->unsignedInteger('user_id')->index();
									 $table->unsignedInteger('updates_id')->index();
									 $table->timestamps();
							 });
			 }// <<--- End Create Table Bookmarks

			//============== Files Affected ================//
			$file1 = 'UpdatesController.php';
			$file2 = 'UserController.php';
			$file3 = 'AdminController.php';
			$file4 = 'HomeController.php';
			$file5 = 'MessagesController.php';
			$file6 = 'SocialAccountService.php';
			$file7 = 'PayPalController.php';

			$file8 = 'UserDelete.php'; // Traits
			$file9 = 'User.php';
			$file10 = 'Bookmarks.php';
			$file11 = 'Updates.php';

			$file12 = 'web.php';

			$file14 = 'bookmarks.blade.php';
			$file15 = 'home-session.blade.php';
			$file16 = 'css_general.blade.php';
			$file17 = 'javascript_general.blade.php';
			$file18 = 'limits.blade.php';
			$file19 = 'navbar.blade.php';
			$file20 = 'footer.blade.php';

			$file21 = 'settings.blade.php';
			$file22 = 'layout.blade.php';
			$file23 = 'updates.blade.php';

			$file24 = 'home.blade.php';
			$file25 = 'profile.blade.php';

			$file26 = 'withdrawals.blade.php';
			$file27 = 'withdrawals.blade.php';
			$file28 = 'social-login.blade.php';
			$file29 = 'app.blade.php';

			$file30 = 'app-functions.js';
			$file31 = 'bootstrap-dark.min.css';

			$file32 = 'bell-light.svg';
			$file33 = 'compass-light.svg';
			$file34 = 'home-light.svg';
			$file35 = 'paper-light.svg';


			//============== Moving Files ================//
			$this->moveFile($path.$file1, $CONTROLLERS.$file1, $copy);
			$this->moveFile($path.$file2, $CONTROLLERS.$file2, $copy);
			$this->moveFile($path.$file3, $CONTROLLERS.$file3, $copy);
			$this->moveFile($path.$file4, $CONTROLLERS.$file4, $copy);
			$this->moveFile($path.$file5, $CONTROLLERS.$file5, $copy);
			$this->moveFile($path.$file6, $APP.$file6, $copy);
			$this->moveFile($path.$file7, $CONTROLLERS.$file7, $copy);

			$this->moveFile($path.$file8, $TRAITS.$file8, $copy);
			$this->moveFile($path.$file9, $MODELS.$file9, $copy);
			$this->moveFile($path.$file10, $MODELS.$file10, $copy);
			$this->moveFile($path.$file11, $MODELS.$file11, $copy);

			$this->moveFile($path.$file12, $ROUTES.$file12, $copy);

			$this->moveFile($path.$file14, $VIEWS_USERS.$file14, $copy);
			$this->moveFile($path.$file15, $VIEWS_INDEX.$file15, $copy);
			$this->moveFile($path.$file16, $VIEWS_INCLUDES.$file16, $copy);
			$this->moveFile($path.$file17, $VIEWS_INCLUDES.$file17, $copy);
			$this->moveFile($path.$file18, $VIEWS_ADMIN.$file18, $copy);
			$this->moveFile($path.$file19, $VIEWS_INCLUDES.$file19, $copy);
			$this->moveFile($path.$file20, $VIEWS_INCLUDES.$file20, $copy);
			$this->moveFile($path.$file21, $VIEWS_ADMIN.$file21, $copy);
			$this->moveFile($path.$file22, $VIEWS_ADMIN.$file22, $copy);
			$this->moveFile($path.$file23, $VIEWS_INCLUDES.$file23, $copy);
			$this->moveFile($path.$file24, $VIEWS_INDEX.$file24, $copy);
			$this->moveFile($path.$file25, $VIEWS_USERS.$file25, $copy);
			$this->moveFile($path.$file26, $VIEWS_USERS.$file26, $copy);
			$this->moveFile($pathAdmin.$file27, $VIEWS_ADMIN.$file27, $copy);
			$this->moveFile($path.$file28, $VIEWS_ADMIN.$file28, $copy);
			$this->moveFile($path.$file29, $VIEWS_LAYOUTS.$file29, $copy);

			$this->moveFile($path.$file30, $PUBLIC_JS.$file30, $copy);
			$this->moveFile($path.$file31, $PUBLIC_CSS.$file31, $copy);

			$this->moveFile($path.$file32, $PUBLIC_IMG_ICONS.$file32, $copy);
			$this->moveFile($path.$file33, $PUBLIC_IMG_ICONS.$file33, $copy);
			$this->moveFile($path.$file34, $PUBLIC_IMG_ICONS.$file34, $copy);
			$this->moveFile($path.$file35, $PUBLIC_IMG_ICONS.$file35, $copy);


			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.2 ----->>

		if ($version == '1.3') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			if (! Schema::hasColumn('admin_settings', 'file_size_allowed_verify_account')) {
						Schema::table('admin_settings', function($table) {
						 $table->unsignedInteger('file_size_allowed_verify_account');
				});

				if (Schema::hasColumn('admin_settings', 'file_size_allowed_verify_account')) {
					AdminSettings::whereId(1)->update([
								'file_size_allowed_verify_account' => 1024
							]);
				}
			}

			//============== Files Affected ================//
			$file3 = 'AdminController.php';
			$file5 = 'MessagesController.php';

			$file8 = 'UserDelete.php'; // Traits

			$file14 = 'verify_account.blade.php';
			$file16 = 'css_general.blade.php';
			$file18 = 'limits.blade.php';

			$file22 = 'dashboard.blade.php';

			$file29 = 'app.blade.php';

			$file30 = 'app-functions.js';
			$file31 = 'messages.js';

			//============== Moving Files ================//
			$this->moveFile($path.$file3, $CONTROLLERS.$file3, $copy);
			$this->moveFile($path.$file5, $CONTROLLERS.$file5, $copy);

			$this->moveFile($path.$file8, $TRAITS.$file8, $copy);

			$this->moveFile($path.$file14, $VIEWS_USERS.$file14, $copy);
			$this->moveFile($path.$file16, $VIEWS_INCLUDES.$file16, $copy);
			$this->moveFile($path.$file18, $VIEWS_ADMIN.$file18, $copy);

			$this->moveFile($path.$file22, $VIEWS_ADMIN.$file22, $copy);

			$this->moveFile($path.$file29, $VIEWS_LAYOUTS.$file29, $copy);

			$this->moveFile($path.$file30, $PUBLIC_JS.$file30, $copy);
			$this->moveFile($path.$file31, $PUBLIC_JS.$file31, $copy);


			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.3 ----->>

		if ($version == '1.4') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			PaymentGateways::whereId(1)->update([
						'recurrent' => 'no',
						'logo' => 'paypal.png',
					]);

					PaymentGateways::whereId(2)->update([
								'logo' => 'stripe.png',
							]);

			//============== Files Affected ================//
			$file3 = 'AdminController.php';
			$file5 = 'UserController.php';
			$file18 = 'storage.blade.php';
			$file29 = 'app.blade.php';


			//============== Moving Files ================//
			$this->moveFile($path.$file3, $CONTROLLERS.$file3, $copy);
			$this->moveFile($path.$file5, $CONTROLLERS.$file5, $copy);
			$this->moveFile($path.$file18, $VIEWS_ADMIN.$file18, $copy);
			$this->moveFile($path.$file29, $VIEWS_LAYOUTS.$file29, $copy);

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.4 ----->>

		if ($version == '1.5') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			//============== Files Affected ================//
			$file5 = 'UserController.php';
			$file6 = 'SocialAccountService.php';
			$file18 = 'updates.blade.php';
			$file29 = 'app.blade.php';
			$file30 = 'profile.blade.php';
			$file31 = 'edit_my_page.blade.php';


			//============== Moving Files ================//
			$this->moveFile($path.$file5, $CONTROLLERS.$file5, $copy);
			$this->moveFile($path.$file6, $APP.$file6, $copy);
			$this->moveFile($path.$file18, $VIEWS_INCLUDES.$file18, $copy);
			$this->moveFile($path.$file29, $VIEWS_LAYOUTS.$file29, $copy);
			$this->moveFile($path.$file30, $VIEWS_USERS.$file30, $copy);
			$this->moveFile($path.$file31, $VIEWS_USERS.$file31, $copy);

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.5 ----->>

		if ($version == '1.6') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || !$this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			if (! Schema::hasColumn('users',
					'gender',
					'birthdate',
					'allow_download_files',
					'language'
				)) {
						Schema::table('users', function($table) {
							$table->string('gender', 50);
 						 	$table->string('birthdate', 30);
						  $table->enum('allow_download_files', ['no', 'yes'])->default('no');
							$table->string('language', 10);
				});
			}

			if (! Schema::hasColumn('transactions', 'type')) {
						Schema::table('transactions', function($table) {
						 $table->enum('type', ['subscription', 'tip', 'ppv'])->default('subscription');
				});
			}

			if (! Schema::hasColumn('admin_settings',
					'payout_method_paypal',
					 'payout_method_bank',
					 'min_tip_amount',
					 'max_tip_amount',
					 'min_ppv_amount',
					 'max_ppv_amount',
					 'min_deposits_amount',
					 'max_deposits_amount',
					 'button_style',
					 'twitter_login',
					 'hide_admin_profile',
					 'requests_verify_account',
					 'navbar_background_color',
					 'navbar_text_color',
					 'footer_background_color',
					 'footer_text_color'

					 )
					) {
						Schema::table('admin_settings', function($table) {
						 $table->enum('payout_method_paypal', ['on', 'off'])->default('on');
						 $table->enum('payout_method_bank', ['on', 'off'])->default('on');
						 $table->unsignedInteger('min_tip_amount');
						 $table->unsignedInteger('max_tip_amount');
						 $table->unsignedInteger('min_ppv_amount');
						 $table->unsignedInteger('max_ppv_amount');
						 $table->unsignedInteger('min_deposits_amount');
						 $table->unsignedInteger('max_deposits_amount');
						 $table->enum('button_style', ['rounded', 'normal'])->default('rounded');
						 $table->enum('twitter_login', ['on', 'off'])->default('off');
						 $table->enum('hide_admin_profile', ['on', 'off'])->default('off');
						 $table->enum('requests_verify_account', ['on', 'off'])->default('on');
						 $table->string('navbar_background_color', 30);
						 $table->string('navbar_text_color', 30);
						 $table->string('footer_background_color', 30);
						 $table->string('footer_text_color', 30);

				});
			}

			file_put_contents(
					'.env',
					"\nTWITTER_CLIENT_ID=\nTWITTER_CLIENT_SECRET=\n",
					FILE_APPEND
			);

			$sql = new Languages();
			$sql->name = 'Español';
			$sql->abbreviation = 'es';
			$sql->save();

			AdminSettings::whereId(1)->update([
						'navbar_background_color' => '#ffffff',
						'navbar_text_color' => '#3a3a3a',
						'footer_background_color' => '#ffffff',
						'footer_text_color' => '#5f5f5f',
						'min_tip_amount' => 5,
						'max_tip_amount' => 99
					]);

			DB::statement("ALTER TABLE reports MODIFY reason ENUM('copyright', 'privacy_issue', 'violent_sexual', 'spoofing', 'spam', 'fraud', 'under_age') NOT NULL");

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.6 ----->>

		if ($version == '1.7') {

			//============ Starting moving files...
			$oldVersion = $this->settings->version;
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			//============== Files Affected ================//
			$file5 = 'UserController.php';
			$file6 = 'RegisterController.php';
			$file18 = 'home-login.blade.php';
			$file29 = 'app.blade.php';
			$file30 = 'password.blade.php';
			$file31 = 'edit_my_page.blade.php';
			$file32 = 'invoice.blade.php';


			//============== Moving Files ================//
			$this->moveFile($path.$file5, $CONTROLLERS.$file5, $copy);
			$this->moveFile($path.$file6, $CONTROLLERS_AUTH.$file6, $copy);
			$this->moveFile($path.$file18, $VIEWS_INDEX.$file18, $copy);
			$this->moveFile($path.$file29, $VIEWS_LAYOUTS.$file29, $copy);
			$this->moveFile($path.$file30, $VIEWS_USERS.$file30, $copy);
			$this->moveFile($path.$file31, $VIEWS_USERS.$file31, $copy);
			$this->moveFile($path.$file32, $VIEWS_USERS.$file32, $copy);

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.7 ----->>

		if ($version == '1.8') {

			//============ Starting moving files...
			$oldVersion = '1.6';
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = false;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion && $this->settings->version != '1.7' || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			if (! Schema::hasColumn('payment_gateways', 'subscription')) {
						Schema::table('payment_gateways', function($table) {
						 $table->enum('subscription', ['yes', 'no'])->default('yes');
				});
			}

			DB::table('payment_gateways')->insert([
				[
					'name' => 'Bank Transfer',
					'type' => 'bank',
					'enabled' => '0',
					'fee' => 0.0,
					'fee_cents' => 0.00,
					'email' => '',
					'key' => '',
					'key_secret' => '',
					'bank_info' => '',
					'recurrent' => 'no',
					'logo' => '',
					'webhook_secret' => '',
					'subscription' => 'no',
					'token' => str_random(150),
			]
	]);

		if (! Schema::hasColumn('admin_settings', 'announcements', 'preloading', 'preloading_image', 'watermark')) {
						Schema::table('admin_settings', function($table) {
						 $table->text('announcements');
						 $table->enum('preloading', ['on', 'off'])->default('off');
						 $table->string('preloading_image', 100);
						 $table->enum('watermark', ['on', 'off'])->default('on');
						 $table->enum('earnings_simulator', ['on', 'off'])->default('on');
				});
			}

			if (! Schema::hasColumn('users', 'free_subscription', 'wallet')) {
						Schema::table('users', function($table) {
						 $table->enum('free_subscription', ['yes', 'no'])->default('no');
						 $table->decimal('wallet', 10, 2);
						 $table->string('tiktok', 200);
						 $table->string('snapchat', 200);
				});
			}

			if (! Schema::hasColumn('updates', 'price', 'youtube', 'vimeo', 'file_name', 'file_size')) {
						Schema::table('updates', function($table) {
						 $table->decimal('price', 10, 2);
						 $table->string('video_embed', 200);
						 $table->string('file_name', 255);
						 $table->string('file_size', 50);
				});
			}

			if (! Schema::hasColumn('subscriptions', 'free')) {
						Schema::table('subscriptions', function($table) {
						 $table->enum('free', ['yes', 'no'])->default('no');
				});
			}

			if (! Schema::hasColumn('messages', 'price', 'tip', 'tip_amount')) {
						Schema::table('messages', function($table) {
						 $table->decimal('price', 10, 2);
						 $table->enum('tip', ['yes', 'no'])->default('no');
						 $table->unsignedInteger('tip_amount');
				});
			}

			// Create table Deposits
			if (! Schema::hasTable('deposits')) {

					Schema::create('deposits', function ($table) {

					$table->engine = 'InnoDB';
					$table->increments('id');
					$table->unsignedInteger('user_id');
					$table->string('txn_id', 200);
					$table->unsignedInteger('amount');
					$table->string('payment_gateway', 100);
					$table->timestamp('date');
					$table->enum('status', ['active', 'pending'])->default('active');
					$table->string('screenshot_transfer', 100);
			});
		}// <<< --- Create table Deposits

			//============== Files Affected ================//
			$files = [
				'UpdatesController.php' => $CONTROLLERS,
				'PayPalController.php' => $CONTROLLERS,
				'AdminController.php' => $CONTROLLERS,
				'HomeController.php' => $CONTROLLERS,
				'MessagesController.php' => $CONTROLLERS,
				'SubscriptionsController.php' => $CONTROLLERS,
				'StripeController.php' => $CONTROLLERS,
				'AddFundsController.php' => $CONTROLLERS,
				'UserController.php' => $CONTROLLERS,
				'InstallScriptController.php' => $CONTROLLERS,
				'Helper.php' => $APP,
				'Subscriptions.php' => $MODELS,
				'Deposits.php' => $MODELS,
				'app.blade.php' => $VIEWS_LAYOUTS,
				'javascript_general.blade.php' => $VIEWS_INCLUDES,
				'home-login.blade.php' => $VIEWS_INDEX,
				'register.blade.php' => $VIEWS_AUTH,
				'notifications.blade.php' => $VIEWS_USERS,
				'my_payments.blade.php' => $VIEWS_USERS,
				'navbar.blade.php' => $VIEWS_INCLUDES,
				'edit-update.blade.php' => $VIEWS_USERS,
				'listing-creators.blade.php' => $VIEWS_INCLUDES,
				'explore_creators.blade.php' => $VIEWS_INCLUDES,
				'listing-explore-creators.blade.php' => $VIEWS_INCLUDES,
				'updates.blade.php' => $VIEWS_INCLUDES,
				'footer-tiny.blade.php' => $VIEWS_INCLUDES,
				'messages-chat.blade.php' => $VIEWS_INCLUDES,
				'footer.blade.php' => $VIEWS_INCLUDES,
				'profile.blade.php' => $VIEWS_USERS,
				'cards-settings.blade.php' => $VIEWS_INCLUDES,
				'subscription.blade.php' => $VIEWS_USERS,
				'messages-inbox.blade.php' => $VIEWS_INCLUDES,
				'css_general.blade.php' => $VIEWS_INCLUDES,
				'invoice.blade.php' => $VIEWS_USERS,
				'my_subscriptions.blade.php' => $VIEWS_USERS,
				'my_subscribers.blade.php' => $VIEWS_USERS,
				'dashboard.blade.php' => $VIEWS_USERS,
				'listing-categories.blade.php' => $VIEWS_INCLUDES,
				'email.blade.php' => $VIEWS_AUTH_PASS,
				'payout_method.blade.php' => $VIEWS_USERS,
				'sitemaps.blade.php' => $VIEWS_INDEX,
				'home-session.blade.php' => $VIEWS_INDEX,
				'form-post.blade.php' => $VIEWS_INCLUDES,
				'edit_my_page.blade.php' => $VIEWS_USERS,
				'home.blade.php' => $VIEWS_INDEX,
				'wallet.blade.php' => $VIEWS_USERS,
				'withdrawals.blade.php' => $VIEWS_USERS,
				'messages-show.blade.php' => $VIEWS_USERS,
				'requirements.blade.php' => $VIEWS_INSTALL,
				'transfer_verification.blade.php' => $VIEWS_EMAILS,
				'verify_account' => $VIEWS_USERS,
				'web.php' => $ROUTES,
				'arial.TTF' => $PUBLIC_FONTS,
				'add-funds.js' => $PUBLIC_JS,
				'app-functions.js' => $PUBLIC_JS,
				'messages.js' => $PUBLIC_JS,
				'payment.js' => $PUBLIC_JS
			];

			$filesAdmin = [
				'verification.blade.php' => $VIEWS_ADMIN,
				'transactions.blade.php' => $VIEWS_ADMIN,
				'posts.blade.php' => $VIEWS_ADMIN,
				'deposits-view.blade.php' => $VIEWS_ADMIN,
				'dashboard.blade.php' => $VIEWS_ADMIN,
				'charts.blade.php' => $VIEWS_ADMIN,
				'deposits.blade.php' => $VIEWS_ADMIN,
				'members.blade.php' => $VIEWS_ADMIN,
				'bank-transfer-settings.blade.php' => $VIEWS_ADMIN,
				'layout.blade.php' => $VIEWS_ADMIN,
				'settings.blade.php' => $VIEWS_ADMIN,
				'payments-settings.blade.php' => $VIEWS_ADMIN
			];

			// Files
			foreach ($files as $file => $root) {
				 $this->moveFile($path.$file, $root.$file, $copy);
			}

			// Files Admin
			foreach ($filesAdmin as $file => $root) {
				 $this->moveFile($pathAdmin.$file, $root.$file, $copy);
			}

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.8 ----->>

		if ($version == '1.9') {

			//============ Starting moving files...
			$oldVersion = '1.8';
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion  || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			// Replace String
			$findStringLang = ');';

			// Ennglish
			$replaceLangEN    = "
	// Version 1.9
	'login_as_user' => 'Login as user',
	'login_as_user_warning' => 'This action will close your current session',
	'become_creator' => 'Become a creator',
);";
			$fileLangEN = 'resources/lang/en/general.php';
			@file_put_contents($fileLangEN, str_replace($findStringLang, $replaceLangEN, file_get_contents($fileLangEN)));

		// Español
		$replaceLangES    = "
	//----- Version 1.9
	'login_as_user' => 'Iniciar sesión como usuario',
	'login_as_user_warning' => 'Esta acción cerrará su sesión actual',
	'become_creator' => 'Conviértete en un creador',
);";
		$fileLangES = 'resources/lang/es/general.php';
		@file_put_contents($fileLangES, str_replace($findStringLang, $replaceLangES, file_get_contents($fileLangES)));

			//============== Files Affected ================//
			$files = [
				'TipController.php' => $CONTROLLERS,
				'UpdatesController.php' => $CONTROLLERS,
				'AdminController.php' => $CONTROLLERS,
				'HomeController.php' => $CONTROLLERS,
				'MessagesController.php' => $CONTROLLERS,
				'UserController.php' => $CONTROLLERS,
				'app.blade.php' => $VIEWS_LAYOUTS,
				'javascript_general.blade.php' => $VIEWS_INCLUDES,
				'navbar.blade.php' => $VIEWS_INCLUDES,
				'listing-creators.blade.php' => $VIEWS_INCLUDES,
				'listing-explore-creators.blade.php' => $VIEWS_INCLUDES,
				'updates.blade.php' => $VIEWS_INCLUDES,
				'profile.blade.php' => $VIEWS_USERS,
				'cards-settings.blade.php' => $VIEWS_INCLUDES,
				'css_general.blade.php' => $VIEWS_INCLUDES,
				'edit_my_page.blade.php' => $VIEWS_USERS,
				'home.blade.php' => $VIEWS_INDEX,
				'messages-show.blade.php' => $VIEWS_USERS,
				'web.php' => $ROUTES,
				'app-functions.js' => $PUBLIC_JS,
				'messages.js' => $PUBLIC_JS,
				'UserDelete.php' => $TRAITS,
				'functions.js' => $PUBLIC_JS_ADMIN
			];

			$filesAdmin = [
				'charts.blade.php' => $VIEWS_ADMIN,
				'deposits.blade.php' => $VIEWS_ADMIN,
				'edit-member.blade.php' => $VIEWS_ADMIN,
				'layout.blade.php' => $VIEWS_ADMIN,
				'reports.blade.php' => $VIEWS_ADMIN
			];

			// Files
			foreach ($files as $file => $root) {
				 $this->moveFile($path.$file, $root.$file, $copy);
			}

			// Files Admin
			foreach ($filesAdmin as $file => $root) {
				 $this->moveFile($pathAdmin.$file, $root.$file, $copy);
			}

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 1.9 ----->>

		if ($version == '2.0') {

			//============ Starting moving files...
			$oldVersion = '1.9';
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion  || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			file_put_contents(
					'.env',
					"\nBACKBLAZE_ACCOUNT_ID=\nBACKBLAZE_APP_KEY=\nBACKBLAZE_BUCKET=\nBACKBLAZE_BUCKET_ID=\n\nVULTR_ACCESS_KEY=\nVULTR_SECRET_KEY=\nVULTR_REGION=\nVULTR_BUCKET=\nVULTR_ENDPOINT=https://ewr1.vultrobjects.com\n\nPWA_SHORT_NAME=\"Sponzy\"\nPWA_ICON_72=public/images/icons/icon-72x72.png\nPWA_ICON_96=public/images/icons/icon-96x96.png\nPWA_ICON_128=public/images/icons/icon-128x128.png\nPWA_ICON_144=public/images/icons/icon-144x144.png\nPWA_ICON_152=public/images/icons/icon-152x152.png\nPWA_ICON_384=public/images/icons/icon-384x384.png\nPWA_ICON_512=public/images/icons/icon-512x512.png\n\nPWA_SPLASH_640=public/images/icons/splash-640x1136.png\nPWA_SPLASH_750=public/images/icons/splash-750x1334.png\nPWA_SPLASH_1125=public/images/icons/splash-1125x2436.png\nPWA_SPLASH_1242=public/images/icons/splash-1242x2208.png\nPWA_SPLASH_1536=public/images/icons/splash-1536x2048.png\nPWA_SPLASH_1668=public/images/icons/splash-1668x2224.png\nPWA_SPLASH_2048=public/images/icons/splash-2048x2732.png\n",
					FILE_APPEND
			);

			if (! Schema::hasColumn('verification_requests', 'form_w9')) {
							Schema::table('verification_requests', function($table) {
							 $table->string('form_w9', 100);
					});
				}

			if ( ! Schema::hasColumn('reserved', 'offline')) {
					\DB::table('reserved')->insert(
						['name' => 'offline']
					);
				}

			if ( ! Schema::hasColumn('admin_settings', 'custom_css', 'custom_js', 'alert_adult')) {
							Schema::table('admin_settings', function($table) {
							 $table->text('custom_css');
							 $table->text('custom_js');
							 $table->enum('alert_adult', ['on', 'off'])->default('off');
					});
				}

			if (Schema::hasTable('payment_gateways')) {
					\DB::table('payment_gateways')->insert([
						[
							'name' => 'CCBill',
							'type' => 'card',
							'enabled' => '0',
							'fee' => 0.0,
							'fee_cents' => 0.00,
							'email' => '',
							'key' => '',
							'key_secret' => '',
							'logo' => '',
							'bank_info' => '',
							'token' => str_random(150),
					],
					[
						'name' => 'Paystack',
						'type' => 'card',
						'enabled' => '0',
						'fee' => 0.0,
						'fee_cents' => 0.00,
						'email' => '',
						'key' => '',
						'key_secret' => '',
						'logo' => '',
						'bank_info' => '',
						'token' => str_random(150),
				]
					]
			);
		}

		if (! Schema::hasColumn('payment_gateways', 'ccbill_accnum', 'ccbill_subacc', 'ccbill_flexid', 'ccbill_salt')) {
					Schema::table('payment_gateways', function($table) {
					 $table->string('ccbill_accnum', 200);
					 $table->string('ccbill_subacc', 200);
					 $table->string('ccbill_flexid', 200);
					 $table->string('ccbill_salt', 200);
			});
		}

			PaymentGateways::whereId(1)->update([
						'recurrent' => 'yes'
					]);

			if (! Schema::hasColumn('users',
					'paystack_plan',
					'paystack_authorization_code',
					'paystack_last4',
					'paystack_exp',
					'paystack_card_brand'
				)) {
						Schema::table('users', function($table) {
						 $table->string('paystack_plan', 100);
						 $table->string('paystack_authorization_code', 100);
						 $table->unsignedInteger('paystack_last4');
						 $table->string('paystack_exp', 50);
						 $table->string('paystack_card_brand', 25);
				});
			}

		if (! Schema::hasColumn('subscriptions', 'subscription_id', 'cancelled')) {
						Schema::table('subscriptions', function($table) {
						 $table->string('subscription_id', 50);
						 $table->enum('cancelled', ['yes', 'no'])->default('no');
				});
			}


			// Replace String
			$findStringLang = ');';

			// Ennglish
			$replaceLangEN    = "
		//----- Version 2.0
		'show_errors' => 'Show Errors',
		'info_show_errors' => 'Recommended only in local or test mode',
		'alert_not_subscription' => 'You must set a price or enable Free Subscription to activate your subscription',
		'activate' => 'Activate',
		'my_cards' => 'My cards',
		'info_my_cards' => 'Cards available in your account',
		'add' => 'Add',
		'expiry' => 'Expiry',
		'powered_by' => 'Powered by',
		'notice_charge_to_card' => 'We will make a one-time charge of :amount when adding your payment card', // Not remove :amount
		'redirected_to_paypal_website' => 'You will be redirected to the PayPal website',
		'subscription_expire' => 'Your subscription will be active until',
		'subscribed_until' => 'Subscribed until',
		'cancel_subscription_paypal' => 'Cancel your subscription from your PayPal account, it will be active until',
		'confirm_cancel_payment' => 'Are you sure you want to cancel this transaction?',
		'test_smtp' => 'If you are using SMTP, do a test on the following link to verify that your data is correct.',
		'alert_paypal_delay' => '(Important: PayPal may have a delay, reload the page or wait a minute, otherwise, contact us)',
		'error_currency' => 'Currency not supported (Only NGN, USD, ZAR or GHS allowed)',
		'custom_css_js' => 'Custom CSS/JS',
		'custom_css' => 'Custom CSS (without <style> tags)',
		'custom_js' => 'Custom JavaScript (without <script> tags)',
		'show_alert_adult' => 'Show alert that the site has adult content',
		'alert_content_adult' => 'Attention! This site contains adult content, by accessing you acknowledge that you are 18 years of age.',
		'i_am_age' => 'I am of age',
		'leave' => 'Leave',
		'pwa_short_name' => 'App short name (Ex: Sponzy)',
		'alert_pwa_https' => 'You must use HTTPS (SSL) for PWA to work.',
		'error_internet_disconnected_pwa' => 'You are currently not connected to any networks.',
		'error_internet_disconnected_pwa_2' => 'Check your connection and try again',
		'complete_profile_alert' => 'To submit a verification request you must complete your profile.',
		'set_avatar' => 'Upload a profile picture',
		'set_cover' => 'Upload a cover image',
		'set_country' => 'Select your country of origin',
		'set_birthdate' => 'Set your date of birth',
		'form_w9' => 'Form W-9',
		'not_applicable' => 'Not applicable',
		'form_w9_required' => 'As a US citizen, you must submit the Form W-9',
		'upload_form_w9' => 'Upload Form W-9',
		'formats_available_verification_form_w9' => 'Invalid format, only :formats are allowed.', // Not remove/edit :formats
);";
			$fileLangEN = 'resources/lang/en/general.php';
			@file_put_contents($fileLangEN, str_replace($findStringLang, $replaceLangEN, file_get_contents($fileLangEN)));

		// Español
		$replaceLangES    = "
	//----- Version 2.0
	'show_errors' => 'Mostrar Errores',
	'info_show_errors' => 'Se recomienda solo en modo local o prueba',
	'alert_not_subscription' => 'Debe establecer un precio o habilitar la Suscripción Gratuita para activar su suscripción',
	'activate' => 'Activar',
	'my_cards' => 'Mis tarjetas',
	'info_my_cards' => 'Tarjetas disponibles en tu cuenta',
	'add' => 'Agregar',
	'expiry' => 'Vencimiento',
	'powered_by' => 'Desarrollado por',
	'notice_charge_to_card' => 'Haremos un cargo único de :amount al agregar su tarjeta de pago', // Not remove :amount
	'redirected_to_paypal_website' => 'Serás redirigido al sitio web de PayPal',
	'subscription_expire' => 'Su suscripción estará activa hasta',
	'subscribed_until' => 'Suscrito hasta',
	'cancel_subscription_paypal' => 'Cancela tu suscripción desde tu cuenta PayPal, estará activa hasta',
	'confirm_cancel_payment' => '¿Estás seguro de que desea cancelar esta transacción?',
	'test_smtp' => 'Si está usando SMTP, haz una prueba en el siguiente enlace para verificar que tus datos sean correctos.',
	'alert_paypal_delay' => '(Importante: PayPal puede tener un retraso, recargue la página o espere un minuto, de lo contrario, contáctenos)',
	'error_currency' => 'Moneda no soportada (Solo se permite NGN, USD, ZAR o GHS)',
	'custom_css_js' => 'CSS/JS Personalizado',
	'custom_css' => 'CSS Personalizado (sin la etiqueta <style>)',
	'custom_js' => 'JavaScript Personalizado (sin la etiqueta <script>)',
	'show_alert_adult' => 'Mostrar alerta que el sitio tiene contenido para adultos',
	'alert_content_adult' => '¡Atención! este sitio contiene contenido para adultos, al acceder usted admite tener 18 años de edad.',
	'i_am_age' => 'Soy mayor de edad',
	'leave' => 'Salir',
	'pwa_short_name' => 'Nombre corto de App (Ej: Sponzy)',
	'alert_pwa_https' => 'Debes usar HTTPS (SSL) para que PWA funcione.',
	'error_internet_disconnected_pwa' => 'Actualmente no estás conectado a ninguna red.',
	'error_internet_disconnected_pwa_2' => 'Verifica tu conexión e intente de nuevo',
	'complete_profile_alert' => 'Para enviar una solicitud de verificación, debe completar su perfil.',
	'set_avatar' => 'Sube una imagen de perfil',
	'set_cover' => 'Sube una imagen de portada',
	'set_country' => 'Selecciona tu país de origen',
	'set_birthdate' => 'Establece tu fecha de nacimiento',
	'form_w9' => 'Formulario W-9',
	'not_applicable' => 'No aplica',
	'form_w9_required' => 'Como ciudadano estadounidense, debe enviar el Formulario W-9',
	'upload_form_w9' => 'Subir Formulario W-9',
	'formats_available_verification_form_w9' => 'Formato no válido, solo se permiten :formats', // Not remove/edit :formats
);";
		$fileLangES = 'resources/lang/es/general.php';
		@file_put_contents($fileLangES, str_replace($findStringLang, $replaceLangES, file_get_contents($fileLangES)));

		//============== Files Affected ================//
		$files = [
			'UpdatesController.php' => $CONTROLLERS,
			'PayPalController.php' => $CONTROLLERS,
			'AdminController.php' => $CONTROLLERS,
			'HomeController.php' => $CONTROLLERS,
			'MessagesController.php' => $CONTROLLERS,
			'PaystackController.php' => $CONTROLLERS,
			'SubscriptionsController.php' => $CONTROLLERS,
			'StripeController.php' => $CONTROLLERS,
			'CommentsController.php' => $CONTROLLERS,
			'LoginController.php' => $CONTROLLERS_AUTH,
			'RegisterController.php' => $CONTROLLERS_AUTH,
			'BlogController.php' => $CONTROLLERS,
			'AddFundsController.php' => $CONTROLLERS,
			'CCBillController.php' => $CONTROLLERS,
			'UserController.php' => $CONTROLLERS,
			'TipController.php' => $CONTROLLERS,
			'Helper.php' => $APP,
			'Subscriptions.php' => $MODELS,
			'User.php' => $MODELS,
			'app.blade.php' => $VIEWS_LAYOUTS,
			'javascript_general.blade.php' => $VIEWS_INCLUDES,
			'home-login.blade.php' => $VIEWS_INDEX,
			'register.blade.php' => $VIEWS_AUTH,
			'login.blade.php' => $VIEWS_AUTH,
			'notifications.blade.php' => $VIEWS_USERS,
			'my_payments.blade.php' => $VIEWS_USERS,
			'navbar.blade.php' => $VIEWS_INCLUDES,
			'listing-creators.blade.php' => $VIEWS_INCLUDES,
			'explore_creators.blade.php' => $VIEWS_INCLUDES,
			'listing-explore-creators.blade.php' => $VIEWS_INCLUDES,
			'updates.blade.php' => $VIEWS_INCLUDES,
			'comments.blade.php' => $VIEWS_INCLUDES,
			'footer-tiny.blade.php' => $VIEWS_INCLUDES,
			'messages-chat.blade.php' => $VIEWS_INCLUDES,
			'footer.blade.php' => $VIEWS_INCLUDES,
			'profile.blade.php' => $VIEWS_USERS,
			'cards-settings.blade.php' => $VIEWS_INCLUDES,
			'subscription.blade.php' => $VIEWS_USERS,
			'messages-inbox.blade.php' => $VIEWS_INCLUDES,
			'css_general.blade.php' => $VIEWS_INCLUDES,
			'my_subscriptions.blade.php' => $VIEWS_USERS,
			'my_cards.blade.php' => $VIEWS_USERS,
			'my_subscribers.blade.php' => $VIEWS_USERS,
			'dashboard.blade.php' => $VIEWS_USERS,
			'listing-categories.blade.php' => $VIEWS_INCLUDES,
			'payout_method.blade.php' => $VIEWS_USERS,
			'home-session.blade.php' => $VIEWS_INDEX,
			'edit_my_page.blade.php' => $VIEWS_USERS,
			'home.blade.php' => $VIEWS_INDEX,
			'wallet.blade.php' => $VIEWS_USERS,
			'withdrawals.blade.php' => $VIEWS_USERS,
			'messages-show.blade.php' => $VIEWS_USERS,
			'verify_account.blade.php' => $VIEWS_USERS,
			'menu-mobile.blade.php' => $VIEWS_INCLUDES,
			'password.blade.php' => $VIEWS_USERS,
			'web.php' => $ROUTES,
			'add-funds.js' => $PUBLIC_JS,
			'serviceworker.js' => $ROOT,
			'app-functions.js' => $PUBLIC_JS,
			'messages.js' => $PUBLIC_JS,
			'core.min.js' => $PUBLIC_JS,
			'payment.js' => $PUBLIC_JS,
			'UserDelete.php' => $TRAITS,
			'Functions.php' => $TRAITS,
			'laravelpwa.php' => $CONFIG,
			'filesystems.php' => $CONFIG,
			'packages.php' => $BOOTSTRAP_CACHE,
			'verify.blade.php' => $VIEWS_EMAILS,
			'VerifyCsrfToken.php' => $MIDDLEWARE,
			'jquery.tagsinput.min.css' => public_path('plugins'.$DS.'tagsinput').$DS
		];

		$filesAdmin = [
			'verification.blade.php' => $VIEWS_ADMIN,
			'css-js.blade.php' => $VIEWS_ADMIN,
			'email-settings.blade.php' => $VIEWS_ADMIN,
			'limits.blade.php' => $VIEWS_ADMIN,
			'transactions.blade.php' => $VIEWS_ADMIN,
			'storage.blade.php' => $VIEWS_ADMIN,
			'deposits-view.blade.php' => $VIEWS_ADMIN,
			'dashboard.blade.php' => $VIEWS_ADMIN,
			'pwa.blade.php' => $VIEWS_ADMIN,
			'deposits.blade.php' => $VIEWS_ADMIN,
			'edit-member.blade.php' => $VIEWS_ADMIN,
			'members.blade.php' => $VIEWS_ADMIN,
			'bank-transfer-settings.blade.php' => $VIEWS_ADMIN,
			'paystack-settings.blade.php' => $VIEWS_ADMIN,
			'ccbill-settings.blade.php' => $VIEWS_ADMIN,
			'layout.blade.php' => $VIEWS_ADMIN,
			'settings.blade.php' => $VIEWS_ADMIN,
			'subscriptions.blade.php' => $VIEWS_ADMIN,
			'payments-settings.blade.php' => $VIEWS_ADMIN,
			'reports.blade.php' => $VIEWS_ADMIN
		];

			// Files
			foreach ($files as $file => $root) {
				 $this->moveFile($path.$file, $root.$file, $copy);
			}

			// Files Admin
			foreach ($filesAdmin as $file => $root) {
				 $this->moveFile($pathAdmin.$file, $root.$file, $copy);
			}

			// Copy Folders
			$filePathPublic1 = $path.'images';
			$pathPublic1 = public_path('images');

			$this->moveDirectory($filePathPublic1, $pathPublic1, $copy);

			// Copy Folders
			$filePathPublic2 = $path.'laravelpwa';
			$pathPublic2 = resource_path('views'.$DS.'vendor'.$DS.'laravelpwa');

			$this->moveDirectory($filePathPublic2, $pathPublic2, $copy);

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return $upgradeDone;

		}
		//<<---- End Version 2.0 ----->>

		if ($version == '2.1') {

			//============ Starting moving files...
			$oldVersion = '2.0';
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = true;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion  || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

		//============== Files Affected ================//
		$files = [
			'UpdatesController.php' => $CONTROLLERS,
			'AdminController.php' => $CONTROLLERS,
			'HomeController.php' => $CONTROLLERS,
			'MessagesController.php' => $CONTROLLERS,
			'PaystackController.php' => $CONTROLLERS,
			'SubscriptionsController.php' => $CONTROLLERS,
			'StripeController.php' => $CONTROLLERS,
			'CommentsController.php' => $CONTROLLERS,
			'StripeWebHookController.php' => $CONTROLLERS,
			'AddFundsController.php' => $CONTROLLERS,
			'CCBillController.php' => $CONTROLLERS,
			'UserController.php' => $CONTROLLERS,
			'TipController.php' => $CONTROLLERS,
			'Helper.php' => $APP,
			'app.blade.php' => $VIEWS_LAYOUTS,
			'notifications.blade.php' => $VIEWS_USERS,
			'navbar.blade.php' => $VIEWS_INCLUDES,
			'listing-creators.blade.php' => $VIEWS_INCLUDES,
			'listing-explore-creators.blade.php' => $VIEWS_INCLUDES,
			'updates.blade.php' => $VIEWS_INCLUDES,
			'comments.blade.php' => $VIEWS_INCLUDES,
			'messages-chat.blade.php' => $VIEWS_INCLUDES,
			'footer.blade.php' => $VIEWS_INCLUDES,
			'profile.blade.php' => $VIEWS_USERS,
			'post-detail.blade.php' => $VIEWS_USERS,
			'edit-update.blade.php' =>  $VIEWS_USERS,
			'messages-inbox.blade.php' => $VIEWS_INCLUDES,
			'my_subscriptions.blade.php' => $VIEWS_USERS,
			'my_subscribers.blade.php' => $VIEWS_USERS,
			'wallet.blade.php' => $VIEWS_USERS,
			'messages-show.blade.php' => $VIEWS_USERS,
			'add-funds.js' => $PUBLIC_JS,
			'payment.js' => $PUBLIC_JS,

		];

		$filesAdmin = [
			'verification.blade.php' => $VIEWS_ADMIN,
			'dashboard.blade.php' => $VIEWS_ADMIN,
			'edit-member.blade.php' => $VIEWS_ADMIN,
			'members.blade.php' => $VIEWS_ADMIN,
			'layout.blade.php' => $VIEWS_ADMIN,
		];

			// Files
			foreach ($files as $file => $root) {
				 $this->moveFile($path.$file, $root.$file, $copy);
			}

			// Files Admin
			foreach ($filesAdmin as $file => $root) {
				 $this->moveFile($pathAdmin.$file, $root.$file, $copy);
			}

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return redirect('panel/admin')
					->withSuccessUpdate(trans('admin.upgrade_done'));

		}
		//<<---- End Version 2.1 ----->>

		if ($version == '2.2') {

			//============ Starting moving files...
			$oldVersion = '2.1';
			$path       = "v$version/";
			$pathAdmin  = "v$version/admin/";
			$copy       = false;

			if ($this->settings->version == $version) {
				return redirect('/');
			}

			if ($this->settings->version != $oldVersion  || ! $this->settings->version) {
				return "<h2 style='text-align:center; margin-top: 30px; font-family: Arial, san-serif;color: #ff0000;'>Error! you must update from version $oldVersion</h2>";
			}

			if ( ! Schema::hasTable('sessions')) {
				Schema::create('sessions', function ($table) {
						$table->string('id')->unique();
						$table->foreignId('user_id')->nullable();
						$table->string('ip_address', 45)->nullable();
						$table->text('user_agent')->nullable();
						$table->text('payload');
						$table->integer('last_activity');
				});
			}

			Helper::envUpdate('SESSION_DRIVER', 'database');

			if ( ! Schema::hasColumn('users', 'notify_new_tip', 'hide_profile', 'hide_last_seen', 'last_login')) {
				 Schema::table('users', function($table) {
					 $table->enum('notify_new_tip', ['yes', 'no'])->default('yes');
					 $table->enum('hide_profile', ['yes', 'no'])->default('no');
					 $table->enum('hide_last_seen', ['yes', 'no'])->default('no');
					 $table->string('last_login', 250);
				 });
			}

			if ( ! Schema::hasColumn('admin_settings', 'genders')) {
							Schema::table('admin_settings', function($table) {
							 $table->string('genders', 250);
					});
				}

			$this->settings->whereId(1)->update([
 					 'genders' => 'male,female'
 				 ]);

			file_put_contents(
					'.env',
					"\nBACKBLAZE_BUCKET_REGION=\n",
					FILE_APPEND
			);

			// Replace String
			$findStringLang = ');';

			// Ennglish
			$replaceLangEN    = "
			// Version 2.2
			'subscribers' => 'Subscriber|Subscribers',
			'cancel_subscription_ccbill' => 'Cancel your subscription from :ccbill, it will be active until', // Not remove/edit :ccbill
			'genders' => 'Genders',
			'genders_required' => 'The genders field is required.',
			'gay' => 'Gay',
			'lesbian' => 'Lesbian',
			'bisexual' => 'Bisexual',
			'transgender' => 'Transgender',
			'metrosexual' => 'Metrosexual',
			'someone_sent_tip' => 'Someone sent me a tip',
			'privacy_security' => 'Privacy and Security',
			'desc_privacy' => 'Set your privacy',
			'hide_profile' => 'Hide profile',
			'hide_last_seen' => 'Hide last seen',
			'login_sessions' => 'Login sessions',
			'last_login_record' => 'Last login record was from',
			'this_device' => 'This device',
			'last_activity' => 'Last activity',
);";
			$fileLangEN = 'resources/lang/en/general.php';
			@file_put_contents($fileLangEN, str_replace($findStringLang, $replaceLangEN, file_get_contents($fileLangEN)));

		// Español
		$replaceLangES    = "
		// Version 2.2
		'subscribers' => 'Suscriptor|Suscriptores',
		'cancel_subscription_ccbill' => 'Cancele su suscripción desde :ccbill, estará activa hasta', // Not remove/edit :ccbill
		'genders' => 'Géneros',
		'genders_required' => 'Géneros es obligatorio',
		'gay' => 'Gay',
		'lesbian' => 'Lesbiana',
		'bisexual' => 'Bisexual',
		'transgender' => 'Transgénero',
		'metrosexual' => 'Metrosexual',
		'someone_sent_tip' => 'Alguien me ha enviado una propina',
		'privacy_security' => 'Privacidad y seguridad',
		'desc_privacy' => 'Configura tu privacidad',
		'hide_profile' => 'Ocultar perfil',
		'hide_last_seen' => 'Ocultar visto por última vez',
		'login_sessions' => 'Sesiones de inicio de sesión',
		'last_login_record' => 'Último registro de inicio de sesión fue desde',
		'this_device' => 'Este dispositivo',
		'last_activity' => 'Última actividad',
);";
		$fileLangES = 'resources/lang/es/general.php';
		@file_put_contents($fileLangES, str_replace($findStringLang, $replaceLangES, file_get_contents($fileLangES)));


		//============== Files Affected ================//
		$files = [
			'InstallScriptController.php' => $CONTROLLERS,
			'AdminController.php' => $CONTROLLERS,
			'HomeController.php' => $CONTROLLERS,
			'MessagesController.php' => $CONTROLLERS,
			'PaystackController.php' => $CONTROLLERS,
			'SubscriptionsController.php' => $CONTROLLERS,
			'StripeController.php' => $CONTROLLERS,
			'CommentsController.php' => $CONTROLLERS,
			'StripeWebHookController.php' => $CONTROLLERS,
			'AddFundsController.php' => $CONTROLLERS,
			'CCBillController.php' => $CONTROLLERS,
			'UserController.php' => $CONTROLLERS,
			'TipController.php' => $CONTROLLERS,
			'Helper.php' => $APP,
			'app.blade.php' => $VIEWS_LAYOUTS,
			'notifications.blade.php' => $VIEWS_USERS,
			'navbar.blade.php' => $VIEWS_INCLUDES,
			'profile.blade.php' => $VIEWS_USERS,
			'post-detail.blade.php' => $VIEWS_USERS,
			'bookmarks.blade.php' => $VIEWS_USERS,
			'form-post.blade.php' => $VIEWS_INCLUDES,
			'my_subscriptions.blade.php' => $VIEWS_USERS,
			'my_subscribers.blade.php' => $VIEWS_USERS,
			'wallet.blade.php' => $VIEWS_USERS,
			'messages-show.blade.php' => $VIEWS_USERS,
			'payment.js' => $PUBLIC_JS,
			'laravelpwa.php' => $CONFIG,
			'Functions.php' => $TRAITS,
			'serviceworker.js' => $ROOT,
			'home-session.blade.php' => $VIEWS_INDEX,
			'paypal-white.png' => public_path('img'.$DS.'payments').$DS,
			'meta.blade.php' => resource_path('views'.$DS.'vendor'.$DS.'laravelpwa'),
			'web.php' => $ROUTES,
			'bootstrap-icons.css' => $PUBLIC_CSS,
			'bootstrap-icons.woff' => $PUBLIC_FONTS,
			'bootstrap-icons.woff2' => $PUBLIC_FONTS,
			'css_general.blade.php' => $VIEWS_INCLUDES,
			'cards-settings.blade.php' => $VIEWS_INCLUDES,
			'plyr.min.js' => public_path('js'.$DS.'plyr').$DS,
			'plyr.css' => public_path('js'.$DS.'plyr').$DS,
			'plyr.polyfilled.min.js' => public_path('js'.$DS.'plyr').$DS,
			'verify_account.blade.php' => $VIEWS_USERS,
			'select2.min.css' => public_path('plugins'.$DS.'select2').$DS,
			'functions.js' => public_path('admin'.$DS.'js').$DS,
			'edit_my_page.blade.php' => $VIEWS_USERS,
			'Notifications.php' => $MODELS,
			'app-functions.js' => $PUBLIC_JS,
			'dashboard.blade.php' => $VIEWS_USERS,
			'subscription.blade.php' => $VIEWS_USERS,
			'my_cards.blade.php' => $VIEWS_USERS,
			'password.blade.php' => $VIEWS_USERS,
			'my_payments.blade.php' => $VIEWS_USERS,
			'payout_method.blade.php' => $VIEWS_USERS,
			'withdrawals.blade.php' => $VIEWS_USERS,
			'privacy_security.blade.php' => $VIEWS_USERS,
			'javascript_general.blade.php' => $VIEWS_INCLUDES,
			'add_payment_card.blade.php' => $VIEWS_USERS,

			];

			$filesAdmin = [
			'verification.blade.php' => $VIEWS_ADMIN,
			'theme.blade.php' => $VIEWS_ADMIN,
			'edit-member.blade.php' => $VIEWS_ADMIN,
			'storage.blade.php' => $VIEWS_ADMIN,
			'settings.blade.php' => $VIEWS_ADMIN,
		];

			// Files
			foreach ($files as $file => $root) {
				 $this->moveFile($path.$file, $root.$file, $copy);
			}

			// Files Admin
			foreach ($filesAdmin as $file => $root) {
				 $this->moveFile($pathAdmin.$file, $root.$file, $copy);
			}

			// Copy UpgradeController
			if ($copy == true) {
				$this->moveFile($path.'UpgradeController.php', $CONTROLLERS.'UpgradeController.php', $copy);
		 }

			// Delete folder
			if ($copy == false) {
			 File::deleteDirectory("v$version");
		 }

			// Update Version
		 $this->settings->whereId(1)->update([
					 'version' => $version
				 ]);

				 // Clear Cache, Config and Views
			\Artisan::call('cache:clear');
			\Artisan::call('config:clear');
			\Artisan::call('view:clear');

			return redirect('panel/admin')
					->withSuccessUpdate(trans('admin.upgrade_done'));

		}
		//<<---- End Version 2.2 ----->>

	}//<--- End Method version

}
