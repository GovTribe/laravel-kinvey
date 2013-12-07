<?php namespace GovTribe\LaravelKinvey\Plugins;

class KinveyGuzzlePlugin {

	/**
	 * Configuration data.
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Create a new plugin instance.
	 *
	 * @param  array $config
	 * @return void
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}
}