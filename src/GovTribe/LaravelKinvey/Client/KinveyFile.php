<?php namespace GovTribe\LaravelKinvey\Client;

use Guzzle\Http\StaticClient;
use Guzzle\Service\Command\CommandInterface;
use GovTribe\LaravelKinvey\Facades\Kinvey;

class KinveyFile {

	/**
	 * Array of parameters returned from Kinvey.
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * Create a new model instance from a command.
	 *
	 * @param CommandInterface
	 * @return string
	 */
	public static function fromCommand(CommandInterface $command)
	{
		$file = new self();
		$file->setParameters($command->getResponse()->json() + array('path' => $command['path']));

		$file->client = $command->getClient();
		return $file->finishUpload();
	}

	/**
	 * Get parameters sent from Kinvey.
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Set parameters sent from Kinvey.
	 *
	 * @param array
	 * @return void
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Perform the file upload operation.
	 *
	 * @param  null
	 * @return $fileID
	 */
	public function finishUpload($requiredHeaders = array())
	{
		$parameters = $this->getParameters();

		if (isset($parameters['_requiredHeaders'])) $requiredHeaders = $parameters['_requiredHeaders'];

		$requiredHeaders = array_merge($requiredHeaders, array('Content-Length' => $parameters['size']));
		extract(parse_url($parameters['_uploadURL']));
		$url = $scheme . '://' . $host . $path;
		parse_str($query, $query);

		$response = StaticClient::put($url, [
			'headers' => $requiredHeaders,
			'query'   => $query,
			'timeout' => 10,
			'body' => $parameters['path'],
		]);

		return $parameters['_id'];
	}
}