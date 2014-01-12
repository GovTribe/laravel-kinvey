<?php namespace GovTribe\LaravelKinvey\Client;

use Illuminate\Support\Facades\Log;
use Guzzle\Service\Client;
use Guzzle\Common\FromConfigInterface;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use Guzzle\Plugin\Backoff\TruncatedBackoffStrategy;
use Guzzle\Plugin\Backoff\ExponentialBackoffStrategy;
use Guzzle\Log\MonologLogAdapter;
use Guzzle\Plugin\Log\LogPlugin;
use Guzzle\Log\MessageFormatter;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseExceptionFactory;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyAuthPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyEntityCreateWithIDPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyCollectionNamePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyExceptionPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyFileMetadataPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyEntityPathRewritePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyUserSoftDeletePlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyRemoveInternalDataPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyInjectEntityIdPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyErrorCodeBackoffStrategy;

class KinveyClient extends Client implements FromConfigInterface {

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
		$client = new self($config['baseURL'], $config);
		$client->setDefaultOption('headers/Content-Type', 'application/json');
		$client->setDefaultOption('X-Kinvey-API-Version', 2);
		$client->setAuthMode($config['defaultAuthMode']);

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
			new KinveyAuthPlugin(),
			new KinveyEntityCreateWithIDPlugin(),
			new KinveyCollectionNamePlugin(),
			new KinveyFileMetadataPlugin(),
			new KinveyEntityPathRewritePlugin(),
			new KinveyUserSoftDeletePlugin(),
			new KinveyRemoveInternalDataPlugin(),
			new KinveyInjectEntityIdPlugin(),
			new KinveyExceptionPlugin(new KinveyResponseExceptionFactory()),
			self::getExponentialBackoffPlugin(
				2,
				array('BLTimeoutError', 'KinveyInternalErrorRetry')
			)
		);

		if ($client->getConfig('logging')) $plugins[] = self::getLogPlugin(Log::getMonolog());

		foreach ($plugins as $plugin) $client->addSubscriber($plugin);

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
	 * Retrieve a truncated exponential backoff plugin that will retry
	 * requests for certain Kinvey error codes.
	 *
	 * @param int   $maxRetries 		Maximum number of retries
	 * @param array $kinveyErrorCodes 	Kinvey error codes to retry
	 *
	 * @return BackoffPlugin
	 */
	public static function getExponentialBackoffPlugin($maxRetries = 0, array $kinveyErrorCodes = null)
	{
		return new BackoffPlugin(
			new TruncatedBackoffStrategy($maxRetries,
				new KinveyErrorCodeBackoffStrategy($kinveyErrorCodes,
					new ExponentialBackoffStrategy()
		)));
	}

	/**
	 * Get the log plugin.
	 *
	 * @return LogPlugin
	 */
	public static function getLogPlugin()
	{
		$adapter = new MonologLogAdapter(Log::getMonolog());
		$format = "[{ts}] \"{method} {resource} {protocol}/{version}\" {code} {phrase} time:{total_time} kinveyRID:{res_header_x-kinvey-request-id} request:{req_body}";
		$formatter = new MessageFormatter($format);

		return new LogPlugin($adapter, $formatter);
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