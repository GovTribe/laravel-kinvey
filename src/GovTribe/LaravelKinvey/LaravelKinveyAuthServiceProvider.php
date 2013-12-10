<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Auth\AuthServiceProvider;
use GovTribe\LaravelKinvey\Auth\AuthManager;
use GovTribe\LaravelKinvey\Eloquent\User;

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

		Event::listen('auth.login', function($user)
		{
			Session::put('kinvey', $user->_kmd['authtoken']);
		});

		Event::listen('auth.logout', function($user)
		{
			Session::forget('kinvey');
		});

		parent::boot();
	}
}