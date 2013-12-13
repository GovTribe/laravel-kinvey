<?php namespace GovTribe\LaravelKinvey\Eloquent;

use Illuminate\Auth\UserInterface;

class User extends Model implements UserInterface {

	/**
	 * The collection associated with the model.
	 *
	 * @var string
	 */
	protected $collection = 'user';

	/**
	 * Suspend users instead of deleting them.
	 *
	 * @var bool
	 */
	protected $softDelete = true;

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->_id;
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{}
}