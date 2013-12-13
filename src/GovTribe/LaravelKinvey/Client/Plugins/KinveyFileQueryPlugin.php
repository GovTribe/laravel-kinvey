<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;
use Log;

class KinveyFileQueryPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * If the query is targets the files collection,
	 * rewrite the URI.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$uri = explode('/', $command->getOperation()->getUri());

		if ($command['collection'] !== 'files') return;
		if ($uri[1] === 'blob') return;

		$uri[1] = 'blob';
		unset($uri[3]);
		$uri = implode('/', $uri);
		$command->getOperation()->setUri($uri);
	}
}
