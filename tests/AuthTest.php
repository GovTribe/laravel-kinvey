<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Database\Eloquent\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
		Kinvey::setAuthMode('admin');

		$user = User::withTrashed()->where('_id', $this->testUser->_id)->first()->forceDelete();

		Kinvey::setAuthMode('app');
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
			$this->assertEquals(Session::get('kinvey'), Auth::user()->_kmd['authtoken'], 'Kinvey auth token is stored in the user session');
			$this->assertEquals('test.guy@foo.com', Auth::user()->username, 'The correct user was logged in');
		}
		else
		{
			$this->assertTrue(false, 'User is not authenticated');
		}

		Auth::logout();

		$this->assertEquals(Auth::check(), false, 'User is not logged in');
		$this->assertEquals(Session::get('kinvey'), null, 'Kinvey auth token is cleared from session');
		$this->assertEquals(null, Auth::user());
	}
}