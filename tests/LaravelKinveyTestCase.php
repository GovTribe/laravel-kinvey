<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Database\Eloquent\User;
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

		if (getenv("TRAVIS"))
		{
			$appName = getenv("KINVEY_APP_NAME");
			$appKey = getenv("KINVEY_APP_KEY");
			$appSecret = getenv("KINVEY_APP_SECRET");
			$masterSecret = getenv("KINVEY_MASTER_SECRET");
			$testMail = getenv("TEST_MAIL");
		}
		else
		{
			extract(include __DIR__.'/TestConfig.php');
		}

		// Kinvey client configuration.
		$this->app['config']->set('kinvey::appName', $appName);
		$this->app['config']->set('kinvey::appKey', $appKey);
		$this->app['config']->set('kinvey::appSecret', $appSecret);
		$this->app['config']->set('kinvey::masterSecret', $masterSecret);

		// Test mail account.
		$this->app['config']->set('kinvey::testMail', $testMail);

		// Eloquent user model.
		$authConfig = $this->app['config']['auth'];
		$authConfig['model'] = 'GovTribe\LaravelKinvey\Database\Eloquent\User';
		$this->app['config']->set('auth', $authConfig);

		// Default database
		$this->app['config']->set('database.default', 'kinvey');

		// Set the auth mode to admin.
		Kinvey::setAuthMode('admin');
	}
}