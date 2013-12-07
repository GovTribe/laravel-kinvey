<?php namespace GovTribe\LaravelKinvey\Facades;

use Illuminate\Support\Facades\Facade;

class Kinvey extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'kinvey';
	}

}