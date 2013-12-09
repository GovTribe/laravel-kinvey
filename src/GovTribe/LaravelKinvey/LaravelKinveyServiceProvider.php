<?php namespace GovTribe\LaravelKinvey;

use Illuminate\Support\ServiceProvider;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Service\Builder\ServiceBuilder;
use GovTribe\LaravelKinvey\Eloquent\Model;
use GovTribe\LaravelKinvey\Eloquent\Connection;

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
		$this->package('govtribe/laravel-kinvey');
		Model::setConnectionResolver($this->app['db']);
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
			return new Connection(array());
		});

	}

	/**
	 * Build the Kinvey API client.
	 *
	 * @return Guzzle\Service\Client
	 */
	public function buildKinveyAPIClient()
	{
		require __DIR__ . '/Service/APIV2Description.php';
		require __DIR__ . '/Service/Builder.php';

		$client = ServiceBuilder::factory($builder)->get('KinveyClient');
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