<?php namespace GovTribe\LaravelKinvey\Client;

use Illuminate\Support\Facades\Session;
use Guzzle\Service\Client;
use Guzzle\Common\FromConfigInterface;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseExceptionFactory;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyAuthPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyEntityCreateWithIDPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyCollectionNamePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyExceptionPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyFileMetadataPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyEntityPathRewritePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyUserSoftDeletePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyRemoveInternalData;

class KinveyClient extends Client implements FromConfigInterface {

	/**
	 * Configuration data.
	 *
	 * @var array
	 */
	protected static $config;

	/**
	 * Collection name this client will query.
	 *
	 * @var string
	 */
	public $collection;

	/**
	 * Authentication mode used by this client.
	 *
	 * @var string
	 */
	protected $authMode = 'app';

	/**
	 * Static factory method used to turn an array or collection of configuration data into an instantiated object.
	 *
	 * @param array|Collection $config Configuration data
	 * @return KinveyClient
	 */
	public static function factory($config = array())
	{
		self::$config = $config;
		$config['debug'] = true;
		$client = new self($config['baseURL'], $config);

		$client->setDefaultOption('headers/Content-Type', 'application/json');
		$client->setDefaultOption('X-Kinvey-API-Version', 2);

		$client = self::registerPlugins($client);

		return $client;
	}

	/**
	 * Register plugin instances in the client.
	 *
	 * @param  KinveyClient
	 * @param  array $plugins
	 * @return KinveyClient
	 */
	public static function registerPlugins(KinveyClient $client, $plugins = array())
	{
		$plugins = array(
			new KinveyAuthPlugin(self::$config),
			new KinveyEntityCreateWithIDPlugin(self::$config),
			new KinveyCollectionNamePlugin(self::$config),
			new KinveyFileMetadataPlugin(self::$config),
			new KinveyEntityPathRewritePlugin(self::$config),
			new KinveyUserSoftDeletePlugin(self::$config),
			new KinveyRemoveInternalData(self::$config),
			new KinveyExceptionPlugin(self::$config, new KinveyResponseExceptionFactory()),
			BackoffPlugin::getExponentialBackoff(3, array(500, 504)),
		);

		foreach ($plugins as $plugin)
		{
			$client->addSubscriber($plugin);
		}

		return $client;
	}

	/**
	 * Set the client's authentication mode.
	 *
	 * @param  string $authMode
	 * @return void
	 */
	public function setAuthMode($authMode)
	{
		$this->authMode = $authMode;
	}

	/**
	 * Get the client's authentication mode.
	 *
	 * @return string
	 */
	public function getAuthMode()
	{
		return $this->authMode;
	}

	/**
	 * Set the collection name used by this client.
	 *
	 * @param  string $collectionName
	 * @return void
	 */
	public function setCollectionName($collectionName)
	{
		$this->collection = $collectionName;
	}

	/**
	 * Get the collection name used by this client.
	 *
	 * @return string
	 */
	public function getCollectionName()
	{
		return $this->collection;
	}

	/**
	 * Magic method used to retrieve a command
	 *
	 * @param string $method Name of the command object to instantiate
	 * @param array  $args   Arguments to pass to the command
	 *
	 * @return mixed Returns the result of the command
	 * @throws BadMethodCallException when a command is not found
	 */
	public function __call($method, $args)
	{
		switch ($method)
		{
			case 'insert':
				$method = 'createEntity';
				break;
			case 'find':
				$method = 'retrieveEntity';
				break;
			case 'update':
				$method = 'updateEntity';
				break;
			case 'remove':
				if (empty($args)) $method = 'deleteCollection';
				break;
		}

		return $this->getCommand($method, isset($args[0]) ? $args[0] : array())->getResult();
	}
}