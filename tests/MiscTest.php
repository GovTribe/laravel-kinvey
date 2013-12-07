<?php

class MiscTest extends LaravelKinveyTestCase {

	/**
	 * Anonymous ping requests.
	 *
	 * @return void
	 */
	public function testPingAnon()
	{
		$response = Kinvey::pingAnon();

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertArrayHasKey('version', $response, 'Response has version key');
		$this->assertArrayHasKey('kinvey', $response, 'Response has kinvey key');
		$this->assertEquals('hello', $response['kinvey'], 'Kinvey key is equal to hello');
	}

	/**
	 * Authenticated ping requests.
	 *
	 * @return void
	 */
	public function testPingAuth()
	{
		$response = Kinvey::pingAuth();

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertArrayHasKey('version', $response, 'Response has version key');
		$this->assertArrayHasKey('kinvey', $response, 'Response has kinvey key');
		$this->assertEquals(
			'hello ' . Kinvey::getConfig('appName'), $response['kinvey'],
			'Kinvey key is equal to hello ' . Kinvey::getConfig('appName')
		);
	}
}