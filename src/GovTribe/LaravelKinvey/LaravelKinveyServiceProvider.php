<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Service\Builder\ServiceBuilder;
use GovTribe\LaravelKinvey\Database\Eloquent\Model;
use GovTribe\LaravelKinvey\Database\Connection;
use Event;

class LaravelKinveyServiceProvider extends ServiceProvider {

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
		$this->package('govtribe/laravel-kinvey', 'kinvey');
		$this->app['config']->set('database.connections.kinvey', array('driver' => 'kinvey'));

		Model::setConnectionResolver($this->app['db']);
		Model::setEventDispatcher($this->app['events']);

		require __DIR__ . '/Events.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['kinvey'] = $this->app->share(function($app)
		{
			return $this->buildKinveyAPIClient();
		});

		$this->app['db']->extend('kinvey', function($config)
		{
			return new Connection($this->app['kinvey']);
		});
	}

	/**
	 * Build the Kinvey API client.
	 *
	 * @return Guzzle\Service\Client
	 */
	public function buildKinveyAPIClient()
	{
		require __DIR__ . '/Client/Service/APIV2Description.php';
		require __DIR__ . '/Client/Service/ServiceBuilder.php';

		$client = ServiceBuilder::factory($serviceBuilder)->get('KinveyClient');
		$client->setDescription(ServiceDescription::factory($APIV2Description));

		return $client;
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('kinvey');
	}

}