<?php namespace GovTribe\LaravelKinvey\Eloquent;

class Widget extends Model {

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->_id;
	}
}