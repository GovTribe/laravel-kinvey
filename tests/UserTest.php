<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;

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
	public function testLogout()
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

}