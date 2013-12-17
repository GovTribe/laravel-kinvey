<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Hashing\HasherInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\EloquentUserProvider as IlluminateEloquentUserProvider;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use GovTribe\LaravelKinvey\Database\Eloquent\User;
use GovTribe\LaravelKinvey\Facades\Kinvey;

class EloquentUserProvider extends IlluminateEloquentUserProvider {

	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Auth\UserInterface  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserInterface $user, array $credentials)
	{
		try
		{
			$result = Kinvey::login($credentials);
			$user->_kmd = $result['_kmd'];
		}
		catch (KinveyResponseException $e)
		{
			if ($e->getResponse()->getStatusCode() === 401)
			{
				return false;
			}
			else
			{
				throw $e;
			}
		}

		return true;
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		Kinvey::setAuthMode('admin');
		$result = parent::retrieveByCredentials($credentials);
		Kinvey::setAuthMode('app');

		return $result;
	}
}