<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

class KinveyRemoveInternalDataPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
{
	/**
	 * Return the array of subscribed events.
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array('command.after_prepare' => 'afterPrepare');
	}

	/**
	 * Remove internal Kinvey metadata from requests.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function afterPrepare(Event $event)
	{
		$command = $event['command'];
		$operation = $command->getOperation();

		if ($command->offsetExists('_kmd')) $command->offsetUnset('_kmd');
		if ($command->offsetExists('_acl')) $command->offsetUnset('_acl');

		if ($operation->getName() !== 'createEntity' && $command->offsetGet('collection') === 'user')
		{
			// These values should never be passed to Kinvey on a user update operation.
			if ($command->offsetExists('username')) $command->offsetUnset('username');
			if ($command->offsetExists('password')) $command->offsetUnset('password');
			if ($command->offsetExists('salt')) $command->offsetUnset('salt');
		}
	}
}
