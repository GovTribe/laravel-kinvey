<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use Guzzle\Http\Exception\ClientErrorResponseException;

class EntityTest extends LaravelKinveyTestCase {

	public function setup()
	{
		parent::setup();
		$this->testEntity = self::createTestEntity();
	}

	public function tearDown()
	{
		self::deleteTestCollection();
	}

	/**
	 * Create entity.
	 *
	 * @return array
	 */
	public function testCreateEntity()
	{
		$this->assertTrue(is_array($this->testEntity), 'Response is array');
		$this->assertArrayHasKey('foo', $this->testEntity, 'Response has foo key');
		$this->assertEquals('bar', $this->testEntity['foo'], 'foo key is equal to bar');
	}

	/**
	 * Retrieve entity.
	 *
	 * @return void
	 */
	public function testRetrieveEntity()
	{
		$command = Kinvey::getCommand('retrieveEntity', array(
			'id' => $this->testEntity['_id'],
			'collection' => 'test',
			'authMode' => 'admin',
		));
		$command->execute();

		$response = $command->getResponse();
		$this->assertEquals('200', $response->getStatusCode(), 'Got correct status code');
	}

	/**
	 * Update Entity.
	 *
	 * @return void
	 */
	public function testUpdateEntity()
	{
		$command = Kinvey::getCommand('updateEntity', array(
			'id' => $this->testEntity['_id'],
			'collection' => 'test',
			'authMode' => 'admin',
			'data' => $this->testEntity + array('new_key' => 'new_value'),
		));

		$response = $command->execute();
		$this->assertEquals('200', $command->getResponse()->getStatusCode(), 'Got correct status code');
		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertArrayHasKey('new_key', $response, 'Response has new_key key');
		$this->assertEquals('new_value', $response['new_key'], 'new_key key is equal to new_value');
	}

	/**
	 * Delete entity.
	 *
	 * @return void
	 */
	public function testDeleteEntity()
	{
		// Delete the entity.
		$command = Kinvey::getCommand('deleteEntity', array(
			'id' => $this->testEntity['_id'],
			'collection' => 'test',
			'authMode' => 'admin',
		));

		$response = $command->execute();
		$this->assertEquals('200', $command->getResponse()->getStatusCode(), 'Got correct status code');
		$this->assertEquals(array('count' => 1), $response, 'Got correct status code');

		// Try to retrieve the deleted entity.
		$command = Kinvey::getCommand('retrieveEntity', array(
			'id' => $this->testEntity['_id'],
			'collection' => 'test',
			'authMode' => 'admin',
		));

		try
		{
			$command->execute();
		}
		catch (ClientErrorResponseException $e)
		{
			$this->assertEquals('404', $e->getResponse()->getStatusCode(), 'Got correct status code');
			return;
		}

		$this->assertTrue(false, 'Entity was not deleted');
	}
}