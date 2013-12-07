<?php namespace GovTribe\LaravelKinvey;

use Guzzle\Service\Client;
use Guzzle\Common\FromConfigInterface;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use GovTribe\LaravelKinvey\Plugins\KinveyAuthPlugin;

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
	 * @return GovTribe\LaravelKinvey\KinveyClient
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
	 * @param  GovTribe\LaravelKinvey\KinveyClient
	 * @param  array $plugins
	 * @return GovTribe\LaravelKinvey\KinveyClient
	 */
	public static function registerPlugins(KinveyClient $client, $plugins = array())
	{
		$plugins = array(
			new KinveyAuthPlugin(self::$config),
			BackoffPlugin::getExponentialBackoff(),
		);

		foreach ($plugins as $plugin)
		{
			$client->addSubscriber($plugin);
		}

		return $client;
	}
}