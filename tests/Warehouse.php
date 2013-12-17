<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Database\Eloquent\Model as Eloquent;

class Warehouse extends Eloquent {

	protected static $unguarded = true;

	public function widgets()
	{
		return $this->hasMany('Widget');
	}
}