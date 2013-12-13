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
		$user = User::withTrashed()->where('_id', $this->testUser->_id)->first()->forceDelete();
		$this->testUser = array();
	}

	/**
	 * Insert user.
	 *
	 * @return void
	 */
	public function testInsertUser()
	{
		$this->assertEquals(true, $this->testUser->exists, 'Exists boolean is true');
		$this->assertTrue(isset($this->testUser->_id), 'User object has _id property');
		$this->assertNotEquals('', (string) $this->testUser->_id, '_id property is not empty');
		$this->assertNotEquals(0, strlen((string) $this->testUser->_id), '_id property is not empty');
		$this->assertInstanceOf('Carbon\Carbon', $this->testUser->created_at);
	}

	/**
	 * Retrieve user.
	 *
	 * @return void
	 */
	public function testRetrieveUser()
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
		$this->assertEquals(false, $this->testUser->exists, 'Exists boolean is false');

		User::onlyTrashed()->where('_id', $this->testUser->_id)->first()->restore();

		$unSuspendedUser = User::find($this->testUser->_id);
		$this->assertInstanceOf('GovTribe\LaravelKinvey\Eloquent\User', $this->testUser, 'User is not suspended');
	}
}