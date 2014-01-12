<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;

class KinveyInjectEntityIdPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * Add the entity's _id to the request body.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$operation = $command->getOperation();

		if ($command->getName() !== 'updateEntity' && $command->offsetExists('_id') === true) return;

		$operation->addParam(new Parameter(array(
			'name' => 'body_id',
			'location' => 'json',
			'type' => 'string',
			'default' => $command->offsetGet('_id'),
			'static' => true,
			'sentAs' => '_id'
		)));
	}
}