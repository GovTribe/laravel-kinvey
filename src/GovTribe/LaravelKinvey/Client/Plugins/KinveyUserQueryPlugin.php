<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;

class KinveyUserQueryPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * If the query is targets the users collection,
	 * rewrite the URI.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function afterPrepare(Event $event)
	{
		$command = $event['command'];
		if ($command->getName() !== 'query') return;
		if (!in_array($command['collection'], array('user', 'users'))) return;

		$command->getRequest()->setPath('/user/' . $this->config['appKey'] . '/');
	}
}
