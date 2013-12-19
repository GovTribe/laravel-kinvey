<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Database\Eloquent\User;
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
		$user = User::withTrashed()->where('_id', $this->testUser->_id)->first()->forceDelete();
		$this->testUser = array();
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
		$this->assertEquals(false, $this->testUser->exists, 'Exists boolean is false');

		User::onlyTrashed()->where('_id', $this->testUser->_id)->first()->restore();

		$unSuspendedUser = User::find($this->testUser->_id);
		$this->assertInstanceOf('GovTribe\LaravelKinvey\Database\Eloquent\User', $this->testUser, 'User is not suspended');
	}

	/**
	 * Create a test user.
	 *
	 * @return GovTribe\LaravelKinvey\Database\Eloquent\User
	 */
	public static function createTestUser()
	{
		Kinvey::setAuthMode('app');
		$user = new User();
		$user->setRawAttributes(array(
			'email'	    => 'test@govtribe.com',
			'first_name'=> 'Test',
			'last_name' => 'Guy',
			'password' 	=> str_random(8),
			'original'  => 'baz'
		));
		$user->save();
		Kinvey::setAuthMode('admin');
		return $user;
	}
}