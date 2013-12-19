<?php namespace GovTribe\LaravelKinvey\Database\Eloquent;

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Model implements UserInterface, RemindableInterface {

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
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();

		User::creating(function($user)
		{
			if (!$user->email) throw new \Exception('User model must contain a not-null email attribute');
			if (!$user->username) $user->username = $user->email;
		});
	}

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
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}
}