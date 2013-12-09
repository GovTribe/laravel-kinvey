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
		return array('GovTribe\LaravelKinvey\LaravelKinveyServiceProvider');
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
		$this->app['config']->set('laravel-kinvey::appName', $appName);
		$this->app['config']->set('laravel-kinvey::baseURL', $hostEndpoint);
		$this->app['config']->set('laravel-kinvey::appKey', $appKey);
		$this->app['config']->set('laravel-kinvey::appSecret', $appSecret);
		$this->app['config']->set('laravel-kinvey::masterSecret', $masterSecret);
		$this->app['config']->set('laravel-kinvey::version', $version);
	}

	/**
	 * Create a test user.
	 *
	 * @return array
	 */
	public static function createTestUser()
	{
		return Kinvey::createUser(
			array(
				'data' => array(
					'username'	=> 'test.guy@foo.com',
					'first_name'=> 'Test',
					'last_name' => 'Guy',
					'password' 	=> str_random(8),
				)
			)
		);
	}

	/**
	 * Delete the test user
	 *
	 * @param  string $id
	 * @return void
	 */
	public static function deleteTestUser($id)
	{
		Kinvey::deleteUser(array(
			'id'	=> $id,
			'hard'	=> 'true',
			'authMode' => 'admin',
		));
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