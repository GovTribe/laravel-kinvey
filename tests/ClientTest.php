<?php namespace Govtribe\LaravelKinvey\Tests;

use GovTribe\LaravelKinvey\Facades\Kinvey;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Client;
use Illuminate\Support\Facades\Config;

class ClientTest extends LaravelKinveyTestCase {

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
		$response = Kinvey::pingAuth(array('authMode' => 'app'));

		$this->assertTrue(is_array($response), 'Response is array');
		$this->assertArrayHasKey('version', $response, 'Response has version key');
		$this->assertArrayHasKey('kinvey', $response, 'Response has kinvey key');
		$this->assertEquals(
			'hello ' . Kinvey::getConfig('appName'), $response['kinvey'],
			'Kinvey key is equal to hello ' . Kinvey::getConfig('appName')
		);
	}

	/**
	 * User login and logout.
	 *
	 * @return void
	 */
	public function testKinveyLoginLogout()
	{
		$testUser = self::createTestUser();

		$userFromCredentials = Kinvey::login(array(
			'username'  => $testUser['username'],
			'password'  => $testUser['password'],
		));
		$this->assertTrue(is_array($userFromCredentials), 'User retrieved is array');

		$userFromToken = Kinvey::me(array(
			'token' => $userFromCredentials['_kmd']['authtoken'],
			'authMode' 	=> 'session',
		));
		$this->assertTrue(is_array($userFromToken), 'User retrieved is array');

		$response = Kinvey::logout(array(
			'token' => $userFromCredentials['_kmd']['authtoken'],
			'authMode' 	=> 'session',
		));
		$this->assertEquals(204, $response->getStatusCode(), 'Logout OK');
	}

	/**
	 * User suspend.
	 *
	 * @return void
	 */
	public function testKinveyUserSuspend()
	{
		$testUser = self::createTestUser();

		Kinvey::deleteEntity(array(
			'collection' => 'user',
			'_id' => $testUser['_id'],
			'hard' => 'false',
			'authMode' => 'admin',
		));

		$suspendedUser = Kinvey::retrieveEntity(array(
			'_id' => $testUser['_id'],
			'collection' => 'user',
			'authMode' => 'admin',
		));

		$this->assertEquals('disabled', $suspendedUser['_kmd']['status']['val'], 'User is suspended');
	}

	/**
	 * User password reset.
	 *
	 * @return void
	 */
	public function testKinveyUserPasswordReset()
	{
		$testUser = self::createTestUser();

		Kinvey::resetPassword(array(
			'_id' => $testUser['_id'],
			'username' => $testUser['username'],
		));

		$checkUser = Kinvey::retrieveEntity(array(
			'_id' => $testUser['_id'],
			'collection' => 'user',
			'authMode' => 'admin',
		));

		$this->assertEquals('InProgress', $checkUser['_kmd']['passwordReset']['status'], 'Password reset in progress');

		//$client = new Client();
		//$response = $client->get(self::getPasswordResetURL())->send();
		//$this->assertEquals(200, $response->getStatusCode(), 'Password reset link is valid');
	}

	/**
	 * Create entity.
	 *
	 * @return array
	 */
	public function testCreateEntity()
	{
		$testEntity = self::createTestEntity();

		$this->assertTrue(is_array($testEntity), 'Response is array');
		$this->assertArrayHasKey('foo', $testEntity, 'Response has foo key');
		$this->assertEquals('bar', $testEntity['foo'], 'foo key is equal to bar');
	}

	/**
	 * Retrieve Entity.
	 *
	 * @return void
	 */
	public function testRetrieveEntity()
	{
		$testEntity = self::createTestEntity();

		$command = Kinvey::getCommand('retrieveEntity', array(
			'_id' => $testEntity['_id'],
			'collection' => 'widgets',
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
		$testEntity = self::createTestEntity();

		$command = Kinvey::getCommand('updateEntity', array(
			'_id' => $testEntity['_id'],
			'collection' => 'widgets',
			'authMode' => 'admin',
		) + $testEntity + array('new_key' => 'new_value'));

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
		$testEntity = self::createTestEntity();

		// Delete the entity.
		$command = Kinvey::getCommand('deleteEntity', array(
			'_id' => $testEntity['_id'],
			'collection' => 'widgets',
			'authMode' => 'admin',
		));

		$response = $command->execute();
		$this->assertEquals('200', $command->getResponse()->getStatusCode(), 'Got correct status code');
		$this->assertEquals(array('count' => 1), $response, 'Got correct status code');

		// Try to retrieve the deleted entity.
		$command = Kinvey::getCommand('retrieveEntity', array(
			'_id' => $testEntity['_id'],
			'collection' => 'widgets',
			'authMode' => 'admin',
		));
		$this->setExpectedException('GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException');
		$command->execute();
	}

	/**
	 * Create file.
	 *
	 * @return void
	 */
	public function testCreateFile()
	{
		$file = Kinvey::createEntity(array(
			'authMode' => 'admin',
			'path' => __DIR__ . '/image.png',
			'collection' => 'files'
		));

		$this->assertTrue(is_array($file), 'Response is array');
		$this->assertEquals(false, empty($file), 'Response is not empty');

		Kinvey::deleteEntity(array(
			'authMode' => 'admin',
			'_id' => $file['_id'],
			'collection' => 'files'
		));
	}

	/**
	 * Retrieve file public.
	 *
	 * @return void
	 */
	public function testRetrieveFilePublic()
	{
		// Public file
		$publicFile = Kinvey::createEntity(array(
			'authMode' => 'admin',
			'path' => __DIR__ . '/image.png',
			'_public' => true,
			'foo' => 'bar',
			'number' => 42,
			'collection' => 'files'
		));

		$file = Kinvey::retrieveEntity(array(
			'authMode' => 'admin',
			'_id' => $publicFile['_id'],
			'collection' => 'files'
		));

		$this->assertTrue(is_array($file), 'Response is array');

		$this->assertArrayHasKey('_public', $file);
		$this->assertEquals(true, $file['_public']);

		$this->assertArrayHasKey('_filename', $file);
		$this->assertEquals('image.png', $file['_filename']);

		$this->assertArrayHasKey('mimeType', $file);
		$this->assertEquals('image/png', $file['mimeType']);

		$this->assertArrayHasKey('foo', $file);
		$this->assertEquals('bar', $file['foo']);

		$this->assertArrayHasKey('number', $file);
		$this->assertEquals('42', $file['number']);

		$this->assertArrayHasKey('_downloadURL', $file);
		$this->assertTrue(is_string($file['_downloadURL']));

		$client = new Client();
		$response = $client->get($file['_downloadURL'])->send();
		$this->assertEquals(200, $response->getStatusCode());

		Kinvey::deleteEntity(array(
			'authMode' => 'admin',
			'_id' => $publicFile['_id'],
			'collection' => 'files'
		));
	}

	/**
	 * Retrieve file private.
	 *
	 * @return void
	 */
	public function testRetrieveFilePrivate()
	{
		$privateFile = Kinvey::createEntity(array(
			'authMode' => 'admin',
			'path' => __DIR__ . '/image.png',
			'_public' => false,
			'collection' => 'files'
		));

		$file = Kinvey::retrieveEntity(array(
			'authMode' => 'admin',
			'_id' => $privateFile['_id'],
			'collection' => 'files'
		));

		$this->assertTrue(is_array($file), 'Response is array');
		$this->assertArrayHasKey('_public', $file);
		$this->assertEquals(false, $file['_public']);

		Kinvey::deleteEntity(array(
			'authMode' => 'admin',
			'_id' => $privateFile['_id'],
			'collection' => 'files'
		));
	}

	/**
	 * Delete file.
	 *
	 * @return void
	 */
	public function testDeleteFile()
	{
		$client = new Client();

		$file = Kinvey::createEntity(array(
			'authMode' => 'admin',
			'path' => __DIR__ . '/image.png',
			'_public' => false,
			'collection' => 'files'
		));

		$file = Kinvey::retrieveEntity(array(
			'authMode' => 'admin',
			'_id' => $file['_id'],
			'collection' => 'files'
		));

		$response = $client->get($file['_downloadURL'])->send();
		$this->assertEquals(200, $response->getStatusCode());

		Kinvey::deleteEntity(array(
			'authMode' => 'admin',
			'_id' => $file['_id'],
			'collection' => 'files'
		));

		$this->setExpectedException('Guzzle\Http\Exception\ClientErrorResponseException');

		$client->get($file['_downloadURL'])->send();
	}

	/**
	 * Restrict entity test.
	 *
	 * @return void
	 */
	public function testRestrictEntity()
	{
		$widget = self::createTestEntity();
		$testUser = self::createTestUser();

		// Login as the test user, attempt to read it.
		$command = Kinvey::getCommand('retrieveEntity', array(
			'collection' => 'widgets',
			'_id' => $widget['_id'],
			'authMode' => 'user',
			'username' => $testUser['username'],
			'password' => $testUser['password'],
		));
		$command->execute();
		$this->assertEquals(200, $command->getResponse()->getStatusCode());

		// Update $widget to make it not globally readable or writable.
		$widget['_acl'] = array_merge($widget['_acl'], array('gr' => false, 'gw' => false));
		$widget['authMode'] = 'admin';
		$widget['collection'] = 'widgets';
		$widget = Kinvey::updateEntity($widget);

		// Login as the test user, attempt to read it.
		$this->setExpectedException('GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException');
		Kinvey::retrieveEntity(array(
			'collection' => 'widgets',
			'_id' => $widget['_id'],
			'authMode' => 'user',
			'username' => $testUser['username'],
			'password' => $testUser['password'],
		));
	}

	/**
	 * Tear down the test environment.
	 *
	 * @return array
	 */
	public function tearDown()
	{
		try
		{
			self::deleteTestCollection();
		}
		catch (KinveyResponseException $e){}

		$users = Kinvey::query(array('collection' => 'user', 'username' => 'test@govtribe.com'));
		if (!empty($users)) Kinvey::deleteEntity(array('collection' => 'user', '_id' => $users[0]['_id'], 'authMode' => 'admin', 'hard' => 'true'));
	}


	/**
	 * Create a test entity.
	 *
	 * @return array
	 */
	public static function createTestEntity()
	{
		return Kinvey::createEntity(array(
			'collection' => 'widgets',
			'authMode' => 'admin',
			'foo' => 'bar',
		));
	}

	/**
	 * Create a test user.
	 *
	 * @return array
	 */
	public static function createTestUser()
	{
		return Kinvey::createEntity(array(
			'collection'=> 'user',
			'email'	    => 'test@govtribe.com',
			'username'	=> 'test@govtribe.com',
			'first_name'=> 'Test',
			'last_name' => 'Guy',
			'password' 	=> str_random(8),
			'original'  => 'baz',
		));
	}

	/**
	 * Create a test group.
	 *
	 * @return array
	 */
	public static function createTestGroup()
	{
		Kinvey::createEntity(array(
			'collection' => 'group',
			'authMode'	 => 'admin',
			'_id' => 'limited',
			'users' => array(
				'all' => false,
				'list' => array(),
			),
		));
	}

	/**
	 * Delete the test collection.
	 *
	 * @param  string $id
	 * @return void
	 */
	public static function deleteTestCollection()
	{
		Kinvey::deleteCollection(array(
			'collection' => 'widgets',
			'authMode' => 'admin',
		));
	}

	/**
	 * Get the password reset URL.
	 *
	 * @return string
	 */
	public static function getPasswordResetURL()
	{
		$options = new \ezcMailImapTransportOptions();
		$options->ssl = true;
		$imap = new \ezcMailImapTransport( 'imap.gmail.com', null, $options );
		$imap->authenticate( 'test@govtribe.com', Config::get('kinvey::testMail') );
		$imap->selectMailbox('Inbox');
		$set = $imap->searchMailbox( 'FROM "support@kinvey.com"' );

		$parser = new \ezcMailParser();
		$mail = $parser->parseMail($set);

		preg_match("#https.*#", $mail[0]->generateBody(), $matches);
		$url = html_entity_decode($matches[0]);
		$url = mb_substr($url, 0, -1);

		foreach ($set->getMessageNumbers() as $msgNum) $imap->delete($msgNum);
		$imap->expunge();

		return $url;
	}

}