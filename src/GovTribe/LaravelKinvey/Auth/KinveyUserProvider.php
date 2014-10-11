<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\EloquentUserProvider as IlluminateEloquentUserProvider;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use GovTribe\LaravelKinvey\Client\KinveyClient;
use Illuminate\Hashing\HasherInterface;

class KinveyUserProvider extends IlluminateEloquentUserProvider implements UserProviderInterface {

	protected $kinvey;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Illuminate\Hashing\HasherInterface  $hasher
	 * @param  string  $model
	 * @param  GovTribe\LaravelKinvey\Client\KinveyClient  $kinvey
	 * @return void
	 */
	public function __construct(HasherInterface $hasher, $model, KinveyClient $kinvey)
	{
		$this->model = $model;
		$this->hasher = $hasher;
		$this->kinvey = $kinvey;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveById($identifier)
	{
		$this->kinvey->setAuthMode('admin');

		$user = $this->createModel()->newQuery()->find($identifier);

		$this->kinvey->setAuthMode('app');

		return $user;
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByToken($identifier, $token)
	{
		//
	}

	/**
	 * Retrieve a user by their unique identifier and "remember me" token.
	 *
	 * @param  mixed  $identifier
	 * @param  string  $token
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function updateRememberToken(UserInterface $user, $token)
	{
		$user->setRememberToken($token);

		$user->save();
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		$this->kinvey->setAuthMode('admin');

		$result = parent::retrieveByCredentials($credentials);

		$this->kinvey->setAuthMode('app');

		return $result;
	}

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
			$result = $this->kinvey->login($credentials);
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
}