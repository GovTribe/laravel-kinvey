<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use \Mockery as m;

class EloquentTest extends LaravelKinveyTestCase {

	/**
	 * Setup the test environment.
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return array
	 */
	public function tearDown()
	{
		\Mockery::close();
	}

	/**
	 * Save a model.
	 *
	 * @return void
	 */
	public function testSave()
	{

	}
}