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

		parent::boot();
	}
}