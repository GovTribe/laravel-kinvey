<?php

use Orchestra\Testbench\TestCase;

abstract class LaravelKinveyTestCase extends TestCase {

	/**
	 * Get package providers.
	 *
	 * @return array
	 */
	protected function getPackageProviders()
	{
		return array('GovTribe\LaravelKinvey\LaravelKinveyServiceProvider');
	}

	/**
	 * Get application aliases.
	 *
	 * @return array
	 */
	protected function getApplicationAliases()
	{
		return parent::getApplicationAliases() + array(
			'Kinvey'	=> 'GovTribe\LaravelKinvey\Facades\Kinvey'
		);
	}

	/**
	 * Create a test user via the signUp() method.
	 *
	 * @return array
	 */
	public static function createTestUser()
	{
		$response = Kinvey::signUp(
			array(
				'data' => array(
					'username'	=> 'test.guy@foo.com',
					'first_name'=> 'Test',
					'last_name' => 'Guy',
					'password' 	=> str_random(8),
				)
			)
		);
		return $response;
	}

	/**
	 * Delete a test user
	 *
	 * @param  string $id
	 * @return void
	 */
	public static function deleteTestUser($id)
	{
		Kinvey::deleteUserAsAdmin(array(
			'id'	=> $id,
			'hard'	=> 'true',
		));
	}

}