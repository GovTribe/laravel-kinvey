<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Hashing\HasherInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\EloquentUserProvider as IlluminateEloquentUserProvider;
use Guzzle\Http\Exception\ClientErrorResponseException;
use GovTribe\LaravelKinvey\Eloquent\User;
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
			$result = Kinvey::login(array('data' => $credentials));
			$user->_kmd = $result['_kmd'];
		}
		catch (ClientErrorResponseException $e)
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