<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\AuthServiceProvider;
use GovTribe\LaravelKinvey\Auth\AuthManager;
use GovTribe\LaravelKinvey\Database\Eloquent\User;

class LaravelKinveyAuthServiceProvider extends AuthServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$app = $this->app;

		$this->app->bind('auth', function($app)
		{
			return new AuthManager($app);
		});

		$this->registerEvents();

		parent::boot();
	}

	/**
	 * Register events for this service provider.
	 *
	 * @return void
	 */
	public function registerEvents()
	{
		//Store the Kinvey auth token in the user's session, and clear it on logout.
		Event::listen('auth.login', function($user)
		{
			Session::put('kinvey', $user->_kmd['authtoken']);
		});

		Event::listen('auth.logout', function($user)
		{
			Session::forget('kinvey');
		});
	}

}