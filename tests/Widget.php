<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Database\Eloquent\Model as Eloquent;

class Widget extends Eloquent {

	protected static $unguarded = true;

	protected $dates = array('expires');

	public function scopeLarge($query)
	{
		return $query->where('model', 'large');
	}

	public function location()
	{
		return $this->belongsTo('Warehouse', 'warehouse_id');
	}

}