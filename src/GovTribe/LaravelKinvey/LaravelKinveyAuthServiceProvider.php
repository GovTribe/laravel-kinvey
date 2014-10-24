<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Auth\AuthServiceProvider as BaseAuthServiceProvider;
use GovTribe\LaravelKinvey\Auth\KinveyUserProvider;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use Event;
use Session;

class LaravelKinveyAuthServiceProvider extends BaseAuthServiceProvider {


	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		$this->registerEvents();
	}

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
			Session::save();
		});

		Event::listen('auth.logout', function($user)
		{
			try
			{
				$this->app->make('kinvey')->logout();
			}
			catch(KinveyResponseException $e){}
		});

		$this->app->error(function(KinveyResponseException $e)
		{
			if ($e->getStatusCode() === 401)
			{
				Session::flush();
			}
		});
	}
}
