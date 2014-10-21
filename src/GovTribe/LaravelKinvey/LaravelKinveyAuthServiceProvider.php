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
			// User logged in via an OAuth provider, we need to log them into Kinvey.
			if (!isset($user->_kmd['authtoken']))
			{
				$credentials = [
					'_socialIdentity' => $user->_socialIdentity,
				];

				$kinveyResponse = $this->app['kinvey']->loginOAuth($credentials);
				$kinveyAuthToken = $kinveyResponse['_kmd']['authtoken'];
			}
			// User logged in directly via Kinvey.
			else $kinveyAuthToken = $user->_kmd['authtoken'];

			Session::put('kinvey', $kinveyAuthToken);
		});

		Event::listen('auth.logout', function($user)
		{
			Session::forget('kinvey');
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
