<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;

use GovTribe\LaravelKinvey\Auth\KinveyUserProvider;

class LaravelKinveyAuthServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$app = $this->app;

		$this->app['auth']->extend('eloquent', function($app)
		{
			return new KinveyUserProvider(
				$app['db']->connection('kinvey'), 
				$app['kinvey'],
				$app['config']['auth']['model']
			);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerEvents();
	}

	/**
	 * Register events for this service provider.
	 *
	 * @return void
	 */
	public function registerEvents()
	{
		//Store the Kinvey auth token in the user's session, and clear it on logout.
		$this->app->events->listen('auth.login', function($user)
		{
			$this->app->session->put('kinvey', $user->_kmd['authtoken']);
		});

		$this->app->events->listen('auth.logout', function($user)
		{
			$this->app->session->forget('kinvey');
		});
	}
}