<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Eloquent\User;
use Illuminate\Support\Facades\Auth;

class AuthTest extends LaravelKinveyTestCase {

	/**
	 * Setup the test environment.
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();
		$this->testUser = UserTest::createTestUser();
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return array
	 */
	public function tearDown()
	{
		$user = User::withTrashed()->where('_id', $this->testUser->_id)->first()->forceDelete();
		$this->testUser = array();
	}

	/**
	 * Test Laravel auth.
	 *
	 * @return void
	 */
	public function testLaravelAuth()
	{
		if (Auth::attempt(array('username' => $this->testUser['username'], 'password' => $this->testUser['password'])))
		{
			$this->assertTrue(true, 'User is authenticated');
		}
		else
		{
			$this->assertTrue(false, 'User is not authenticated');
		}
	}
}