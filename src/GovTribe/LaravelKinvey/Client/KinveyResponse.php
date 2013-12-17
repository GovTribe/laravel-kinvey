<?php namespace GovTribe\LaravelKinvey\Client;

use Guzzle\Service\Command\OperationCommand;

class KinveyResponse {

	/**
	 * Create a new model instance from a command.
	 *
	 * @param OperationCommand
	 */
	public static function fromCommand(OperationCommand $command)
	{
		return $command->getResponse()->json() + array('ok' => true);
	}
}