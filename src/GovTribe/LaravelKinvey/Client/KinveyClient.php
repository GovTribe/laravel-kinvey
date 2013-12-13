<?php namespace GovTribe\LaravelKinvey\Client;

use Guzzle\Service\Client;
use Guzzle\Common\FromConfigInterface;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseExceptionFactory;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyAuthPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyEntityCreateWithIDPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyUserQueryPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyFileQueryPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyExceptionPlugin;
use GovTribe\LaravelKinvey\Client\Plugins\KinveyFileMetadataPlugin;

class KinveyClient extends Client implements FromConfigInterface {

	/**
	 * Configuration data.
	 *
	 * @var array
	 */
	protected static $config;

	/**
	 * Static factory method used to turn an array or collection of configuration data into an instantiated object.
	 *
	 * @param array|Collection $config Configuration data
	 * @return GovTribe\LaravelKinvey\Client\KinveyClient
	 */
	public static function factory($config = array())
	{
		self::$config = $config;
		$client = new self($config['baseURL'], $config);
		$client->setDefaultOption('headers/Content-Type', 'application/json');
		$client->setDefaultOption('X-Kinvey-API-Version', 2);

		return self::registerPlugins($client);
	}

	/**
	 * Register plugin instances in the client.
	 *
	 * @param  GovTribe\LaravelKinvey\Client\KinveyClient
	 * @param  array $plugins
	 * @return GovTribe\LaravelKinvey\Client\KinveyClient
	 */
	public static function registerPlugins(KinveyClient $client, $plugins = array())
	{
		$plugins = array(
			new KinveyAuthPlugin(self::$config),
			new KinveyEntityCreateWithIDPlugin(self::$config),
			new KinveyUserQueryPlugin(self::$config),
			new KinveyFileQueryPlugin(self::$config),
			new KinveyFileMetadataPlugin(self::$config),
			new KinveyExceptionPlugin(self::$config, new KinveyResponseExceptionFactory()),
			BackoffPlugin::getExponentialBackoff(),
		);

		foreach ($plugins as $plugin)
		{
			$client->addSubscriber($plugin);
		}

		return $client;
	}
}