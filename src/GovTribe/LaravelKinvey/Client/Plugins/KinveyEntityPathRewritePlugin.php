<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;

class KinveyEntityPathRewritePlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * If the query is targets the users or files collection,
	 * rewrite the URI. This allows the same entity commands to be used
	 * for data store entities as well as users and files.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$operation = $command->getOperation();

		if ($command->offsetGet('collection') === 'user' || $command->getClient()->getCollectionName() === 'user')
		{
			if (strstr($operation->getURI(), 'user') !== false) return;

			$operation->setURI('/user/{appKey}/{_id}');
		}
		elseif ($command->offsetGet('collection') === 'files' || $command->getClient()->getCollectionName() === 'files')
		{
			if (strstr($operation->getURI(), 'files') !== false) return;
			$operation->setURI('/blob/{appKey}/{_id}');
			$command->getRequestHeaders()->add('X-Kinvey-API-Version', 3);
		}
		elseif ($command->offsetGet('collection') === 'group' || $command->getClient()->getCollectionName() === 'group')
		{
			if (strstr($operation->getURI(), 'group') !== false) return;
			$operation->setURI('/group/{appKey}/{_id}');
		}
	}
}
