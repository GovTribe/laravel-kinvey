<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;
use Symfony\Component\HttpFoundation\File\File;

class KinveyFileMetadataPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
{

	/**
	 * File metadata.
	 *
	 * @var array
	 */
	public $metadata = array(
		'path', '_public', '_filename',
		'size', 'mimeType'
	);

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
		if ($command->getName() !== 'createEntity') return;
		if ($command['collection'] !== 'files') return;

		$data = $command['data'];

		$file = $file = new File($data['path']);

		$operation->setResponseClass('GovTribe\LaravelKinvey\Client\KinveyFile');

		$operation->addParam(new Parameter(array(
			'name' => 'path',
			'type' => 'string',
			'default' => $data['path'],
		)));

		$operation->addParam(new Parameter(array(
			'name' => '_public',
			'location' => 'json',
			'type' => 'boolean',
			'default' => isset($data['_public']) ? $data['_public'] : false,
		)));

		$operation->addParam(new Parameter(array(
			'name' => '_filename',
			'location' => 'json',
			'type' => 'string',
			'default' => !isset($data['_filename']) ? $file->getBaseName() : $data['_filename'],
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

		// Add dynamic properties that the user may have passed in
		// along with the file.
		foreach ($data as $key => $value)
		{
			if (in_array($key, $this->metadata)) continue;

			$operation->addParam(new Parameter(array(
				'name' => $key,
				'location' => 'json',
				'type' => gettype($value),
				'default' => $value,
			)));
		}
	}
}
