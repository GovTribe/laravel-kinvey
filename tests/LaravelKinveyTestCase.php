<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use Orchestra\Testbench\TestCase;

abstract class LaravelKinveyTestCase extends TestCase {

	/**
	 * Get package providers.
	 *
	 * @return array
	 */
	protected function getPackageProviders()
	{
		return array(
			'GovTribe\LaravelKinvey\LaravelKinveyServiceProvider',
			'GovTribe\LaravelKinvey\LaravelKinveyAuthServiceProvider',
		);
	}

	/**
	 * Get application aliases.
	 *
	 * @return array
	 */
	protected function getApplicationAliases()
	{
		return parent::getApplicationAliases() + array(
			'Kinvey'	=> 'GovTribe\LaravelKinvey\Facades\Kinvey'
		);
	}

	/**
	 * Setup the test environment.
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();

		extract(include __DIR__.'/TestConfig.php');

		// Kinvey client configuration.
		$this->app['config']->set('kinvey::appName', $appName);
		$this->app['config']->set('kinvey::baseURL', $hostEndpoint);
		$this->app['config']->set('kinvey::appKey', $appKey);
		$this->app['config']->set('kinvey::appSecret', $appSecret);
		$this->app['config']->set('kinvey::masterSecret', $masterSecret);
		$this->app['config']->set('kinvey::version', $version);

		// Eloquent user model.
		$authConfig = $this->app['config']['auth'];
		$authConfig['model'] = 'GovTribe\LaravelKinvey\Eloquent\User';
		$this->app['config']->set('auth', $authConfig);
	}

	/**
	 * Create a test entity.
	 *
	 * @return array
	 */
	public static function createTestEntity()
	{
		return Kinvey::createEntity(array(
			'collection' => 'test',
			'authMode' => 'admin',
			'data' => array(
				'foo' => 'bar',
			),
		));
	}

	/**
	 * Delete the test collection.
	 *
	 * @param  string $id
	 * @return void
	 */
	public static function deleteTestCollection()
	{
		$response = Kinvey::deleteCollection(array(
			'collectionName' => 'test',
			'authMode' => 'admin',
		));
	}


}