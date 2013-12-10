<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Eloquent\User;
use Illuminate\Support\Facades\Auth;

class UserTest extends LaravelKinveyTestCase {

	/**
	 * Setup the test environment.
	 *
	 * @return array
	 */
	public function setup()
	{
		parent::setup();
		$this->testUser = self::createTestUser();
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return array
	 */
	public function tearDown()
	{
		User::withTrashed()->where('_id', $this->testUser->_id)->first()->forceDelete();
		$this->testUser = array();
	}

	/**
	 * Login user.
	 *
	 * @return void
	 */
	public function testKinveyLogin()
	{
		$userFromCredentials = Kinvey::login(array(
			'data' => array(
				'username'  => $this->testUser['username'],
				'password'  => $this->testUser['password'],
			)
		));

		$this->assertTrue(is_array($userFromCredentials), 'User retrieved is array');

		$userFromToken = Kinvey::me(array(
			'token' => $userFromCredentials['_kmd']['authtoken'],
			'authMode' 	=> 'session',

		));

		$this->assertTrue(is_array($userFromToken), 'User retrieved is array');
	}

	/**
	 * Logout user.
	 *
	 * @return void
	 */
	public function testKinveyLogout()
	{
		$userFromCredentials = Kinvey::login(array(
			'data' => array(
				'username'  => $this->testUser['username'],
				'password'  => $this->testUser['password'],
			)
		));

		$response = Kinvey::logout(array(
			'token' => $userFromCredentials['_kmd']['authtoken'],
			'authMode' 	=> 'session',
		));

		$this->assertEquals(204, $response->getStatusCode(), 'Logout OK');
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

		Auth::logout();

		if (Auth::check())
		{
			$this->assertTrue(true, 'User is logged out');
		}
		else
		{
			$this->assertTrue(false, 'User is not logged out');
		}

	}

	/**
	 * Retrieve user.
	 *
	 * @return void
	 */
	public function testRetrievUser()
	{
		$user = User::find($this->testUser->_id);
		$this->assertInstanceOf('GovTribe\LaravelKinvey\Eloquent\User', $user, 'User retrieved is instance of GovTribe\LaravelKinvey\Eloquent\User');
	}

	/**
	 * Update user.
	 *
	 * @return void
	 */
	public function testUpdateUser()
	{
		$this->testUser->foo = 'bar';
		$this->testUser->save();

		$updatedAttributes = $this->testUser->getAttributes();

		$this->assertArrayHasKey('foo', $updatedAttributes, 'Updated user has new key');
		$this->assertEquals('bar', $updatedAttributes['foo'], 'New key has correct value');
	}

	/**
	 * Suspend user.
	 *
	 * @return void
	 */
	public function testSuspendUser()
	{
		$this->testUser->delete();

		$suspendedUser = User::find($this->testUser->_id);
		$this->assertEquals(null, $suspendedUser, 'User is suspended');

		User::onlyTrashed()->where('_id', $this->testUser->_id)->first()->restore();

		$unSuspendedUser = User::find($this->testUser->_id);
		$this->assertInstanceOf('GovTribe\LaravelKinvey\Eloquent\User', $this->testUser, 'User is not suspended');
	}

	/**
	 * Create a test user.
	 *
	 * @return GovTribe\LaravelKinvey\Eloquent\User
	 */
	public static function createTestUser()
	{
		$user = new User();
		$user->setRawAttributes(array(
			'username'	=> 'test.guy@foo.com',
			'first_name'=> 'Test',
			'last_name' => 'Guy',
			'password' 	=> str_random(8),
		));
		$user->save();
		return $user;
	}
}