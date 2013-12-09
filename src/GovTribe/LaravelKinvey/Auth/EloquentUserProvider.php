<?php namespace GovTribe\LaravelKinvey\Auth;

use Illuminate\Hashing\HasherInterface;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\UserInterface;
use GovTribe\LaravelKinvey\Facades\Kinvey;
use Guzzle\Http\Exception\ClientErrorResponseException;

class EloquentUserProvider implements UserProviderInterface {

	/**
	 * The hasher implementation.
	 *
	 * @var \Illuminate\Hashing\HasherInterface
	 */
	protected $hasher;

	/**
	 * The Eloquent user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Illuminate\Hashing\HasherInterface  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct(HasherInterface $hasher, $model)
	{
		$this->model = $model;
		$this->hasher = $hasher;
	}

	/**
	 * Retrieve a user by their unique identifier.
	 *
	 * @param  mixed  $identifier
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveById($identifier)
	{
		return $this->createModel()->newQuery()->find($identifier);
	}

	/**
	 * Retrieve a user by the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\UserInterface|null
	 */
	public function retrieveByCredentials(array $credentials)
	{
		// First we will add each credential element to the query as a where clause.
		// Then we can execute the query and, if we found a user, return it in a
		// Eloquent User "model" that will be utilized by the Guard instances.
		$query = $this->createModel()->newQuery();

		foreach ($credentials as $key => $value)
		{
			if ( ! str_contains($key, 'password')) $query->where($key, $value);
		}

		return $query->first();
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
		$plain = $credentials['password'];

		try
		{
			$result = Kinvey::login(array('data' => $credentials));
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

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

}