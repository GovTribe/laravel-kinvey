<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;

class KinveyCollectionNamePlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * Inject the correct collection name for entity queries.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$operation = $event['command']->getOperation();
		$client = $event['command']->getClient();

		if ($operation->hasParam('collection') === false) return;
		if (is_null($client->getCollectionName())) return;

		$operation->getParam('collection')->setDefault($client->getCollectionName());
		$operation->getParam('collection')->setStatic(true);

	}
}
