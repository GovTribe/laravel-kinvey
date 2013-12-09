<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use GovTribe\LaravelKinvey\Auth\AuthManager;
use App;

class LaravelKinveyAuthServiceProvider extends AuthServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		App::bind('auth', function($app)
		{
			return new AuthManager($app);
		});

		parent::boot();
	}
}