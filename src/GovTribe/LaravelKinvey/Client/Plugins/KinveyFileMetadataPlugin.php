<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Symfony\Component\HttpFoundation\File\File;

class KinveyFileMetadataPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
{
	/**
	 * Return the array of subscribed events.
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array('command.before_prepare' => 'beforePrepare');
	}

	/**
	 * Add parameters Kinvey needs to properly process the file.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$operation = $command->getOperation();
		$client = $command->getClient();

		if ($command->getName() !== 'createEntity') return;
		if ($command['collection'] !== 'files' && $client->getCollectionName() !== 'files') return;

		$file = new File($command['path']);

		$operation->setResponseClass('GovTribe\LaravelKinvey\Client\KinveyFileResponse');

		$operation->addParam(new Parameter(array(
			'name' => 'path',
			'type' => 'string',
			'default' => $command['path'],
		)));

		$operation->addParam(new Parameter(array(
			'name' => '_public',
			'location' => 'json',
			'type' => 'boolean',
			'default' => isset($command['_public']) ? $command['_public'] : false,
		)));

		$operation->addParam(new Parameter(array(
			'name' => '_filename',
			'location' => 'json',
			'type' => 'string',
			'default' => !isset($command['_filename']) ? $file->getBaseName() : $command['_filename'],
		)));

		$operation->addParam(new Parameter(array(
			'name' => 'size',
			'location' => 'json',
			'type' => 'integer',
			'default' => $file->getSize(),
		)));

		$operation->addParam(new Parameter(array(
			'name' => 'mimeType',
			'location' => 'json',
			'type' => 'string',
			'default' => $file->getMimeType(),
		)));
	}
}