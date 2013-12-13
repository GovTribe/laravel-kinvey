<?php namespace GovTribe\LaravelKinvey\Client;

use Guzzle\Http\Client;
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
	 * @param array
	 * @return $fileID
	 */
	public function finishUpload(array $requiredHeaders = array())
	{
		$parameters = $this->getParameters();
		if (isset($parameters['_requiredHeaders'])) $requiredHeaders = $parameters['_requiredHeaders'];

		$client = new Client();
		$response = $client->put($parameters['_uploadURL'], $requiredHeaders, fopen($parameters['path'], 'r'))->send();
		return $parameters['_id'];
	}
}