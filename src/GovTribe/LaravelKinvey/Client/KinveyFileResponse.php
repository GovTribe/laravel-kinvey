<?php namespace GovTribe\LaravelKinvey\Client;

use Guzzle\Http\Client;
use Guzzle\Service\Command\CommandInterface;
use GovTribe\LaravelKinvey\Facades\Kinvey;

class KinveyFileResponse {

	/**
	 * Array of parameters returned from Kinvey.
	 *
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * Create a new model instance from a command.
	 *
	 * @param  CommandInterface
	 * @return array
	 */
	public static function fromCommand(CommandInterface $command)
	{
		$file = new self();
		$file->setParameters($command->getResponse()->json() + array('path' => $command['path']));
		$file->finishUpload();
		return $file->getParameters();
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
	 * @param  array
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
	 * @return void
	 */
	public function finishUpload($requiredHeaders = array())
	{
		$parameters = $this->getParameters();

		if (isset($parameters['_requiredHeaders'])) $requiredHeaders = $parameters['_requiredHeaders'];

		$client = new Client;
		$request = $client->put($parameters['_uploadURL'], $requiredHeaders, fopen($parameters['path'], 'r'));
		$response = $request->send();

		unset($parameters['path']);
		$this->setParameters($parameters);
	}
}