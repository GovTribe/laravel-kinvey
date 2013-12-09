<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Auth\Guard;
use Illuminate\Auth\AuthManager as IlluminateAuthManager;
use GovTribe\LaravelKinvey\Auth\EloquentUserProvider;

class AuthManager extends IlluminateAuthManager {

	/**
	 * Create an instance of the Eloquent driver.
	 *
	 * @return \Illuminate\Auth\Guard
	 */
	public function createEloquentDriver()
	{
		$provider = $this->createEloquentProvider();

		return new Guard($provider, $this->app['session.store']);
	}

	/**
	 * Create an instance of the Eloquent user provider.
	 *
	 * @return \Illuminate\Auth\EloquentUserProvider
	 */
	protected function createEloquentProvider()
	{
		$model = $this->app['config']['auth.model'];

		return new EloquentUserProvider($this->app['hash'], $model);
	}
}