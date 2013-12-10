<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;
use Log;

class KinveyUserQueryPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * If the query is targets the users collection,
	 * rewrite the URI. If the operation creates a user,
	 * set the authMode to 'app'.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$uri = explode('/', $command->getOperation()->getUri());

		if ($command['collection'] !== 'user') return;
		if ($uri[1] === 'user') return;

		$uri[1] = 'user';
		unset($uri[3]);
		$uri = implode('/', $uri);
		$command->getOperation()->setUri($uri);
	}
}
