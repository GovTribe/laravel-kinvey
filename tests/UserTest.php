<?php

class UserTest extends LaravelKinveyTestCase {

	public function setup()
	{
		parent::setup();
		$this->testUser = self::createTestUser();
	}

	public function tearDown()
	{
		self::deleteTestUser($this->testUser['_id']);
	}

	/**
	 * User sign-up.
	 *
	 * @return array
	 */
	public function testSignUp()
	{
		$this->assertTrue(is_array($this->testUser), 'Response is array');

		$this->assertArrayHasKey('username', $this->testUser, 'Response has username key');
		$this->assertEquals('test.guy@foo.com', $this->testUser['username'], 'username key is equal to test.guy@foo.com');

		$this->assertArrayHasKey('first_name', $this->testUser, 'Response has first_name key');
		$this->assertEquals('Test', $this->testUser['first_name'], 'first_name key is equal to Test');

		$this->assertArrayHasKey('last_name', $this->testUser, 'Response has last_name key');
		$this->assertEquals('Guy', $this->testUser['last_name'], 'last_name key is equal to Guy');

		$this->assertArrayHasKey('password', $this->testUser, 'Response has password key');
	}

	/**
	 * Retrieve user.
	 *
	 * @return void
	 */
	public function testRetrieveUser()
	{
		$retrievedUsers = array(
			'self' => Kinvey::retrieveUser(array(
				'id'        => $this->testUser['_id'],
				'username'  => $this->testUser['username'],
				'password'  => $this->testUser['password'],
			)),
			'admin' => Kinvey::retrieveUserAsAdmin(array(
				'id' => $this->testUser['_id'],
			)),
		);

		foreach ($retrievedUsers as $method => $retrievedUser)
		{
			$this->assertTrue(is_array($retrievedUser), 'User retrieved by ' . $method . ' is array');
			$this->assertArrayHasKey('_id', $retrievedUser, 'User retrieved by ' . $method . ' has an _id key');
			$this->assertEquals($this->testUser['_id'], $retrievedUser['_id'], 'User retrieved by ' . $method . ' is the correct user');
		}
	}

	/**
	 * Update user.
	 *
	 * @return void
	 */
	public function testUpdateUser()
	{
		$updatedUser = Kinvey::updateUser(array(
			'id'        => $this->testUser['_id'],
			'username'  => $this->testUser['username'],
			'password'  => $this->testUser['password'],
			'data' 		=> $this->testUser + array('self' => 'new_value_added_by_user')
		));

		$updatedUser = Kinvey::updateUserAsAdmin(array(
			'id'        => $this->testUser['_id'],
			'data' 		=> $updatedUser + array('admin' => 'new_value_added_by_admin')
		));

		$updatedUser = Kinvey::retrieveUserAsAdmin(array('id' => $this->testUser['_id']));

		$this->assertArrayHasKey('self', $updatedUser, 'Updated user has a value added by the user.');
		$this->assertEquals('new_value_added_by_user', $updatedUser['self'], 'Updated user contains the new value added by the user');
		$this->assertArrayHasKey('admin', $updatedUser, 'Updated user has a value added by the admin.');
		$this->assertEquals('new_value_added_by_admin', $updatedUser['admin'], 'Updated user contains the new value added by the admin');
	}

	/**
	 * Delete user.
	 *
	 * @return void
	 */
	public function testDeleteUser()
	{
		Kinvey::deleteUser(array(
			'id'        => $this->testUser['_id'],
			'username'  => $this->testUser['username'],
			'password'  => $this->testUser['password'],
		));

		$user = Kinvey::retrieveUserAsAdmin(array(
			'id' => $this->testUser['_id'],
		));
		$this->assertEquals($user['_kmd']['status']['val'], 'disabled', 'User is disabled');
	}

	/**
	 * Login user.
	 *
	 * @return void
	 */
	public function testLogin()
	{
		$userFromCredentials = Kinvey::login(array(
			'data' => array(
				'username'  => $this->testUser['username'],
				'password'  => $this->testUser['password'],
			)
		));

		$this->assertTrue(is_array($userFromCredentials), 'User retrieved is array');

		$userFromToken = Kinvey::me(array(
			'token' => $userFromCredentials['_kmd']['authtoken']
		));

		$this->assertTrue(is_array($userFromToken), 'User retrieved is array');
	}

	/**
	 * Logout user.
	 *
	 * @return void
	 */
	public function testLogout()
	{
		$userFromCredentials = Kinvey::login(array(
			'data' => array(
				'username'  => $this->testUser['username'],
				'password'  => $this->testUser['password'],
			)
		));

		$response = Kinvey::logout(array(
			'token' => $userFromCredentials['_kmd']['authtoken']
		));

		$this->assertEquals(204, $response->getStatusCode(), 'Logout OK');
	}

}