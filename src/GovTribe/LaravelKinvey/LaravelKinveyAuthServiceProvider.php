<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Auth\AuthServiceProvider as BaseAuthServiceProvider;
use GovTribe\LaravelKinvey\Auth\KinveyUserProvider;

class LaravelKinveyAuthServiceProvider extends BaseAuthServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		parent::register();

		$this->app->auth->extend('kinvey', function($app)
		{
			return new KinveyUserProvider(
				$this->app->make('hash'),
				$this->app->config['auth.model'],
				$this->app->make('kinvey')
			);
		});
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