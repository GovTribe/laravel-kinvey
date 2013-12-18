<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseExceptionFactory;

class KinveyExceptionPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
{
	/**
	 * Factory used to create new exceptions
	 *
	 * @var KinveyResponseExceptionFactory
	 */
	protected $factory;

	/**
	 * Factory used to create new exceptions
	 *
	 * @param KinveyResponseExceptionFactory
	 * @return KinveyExceptionPlugin
	 */
	public function __construct(KinveyResponseExceptionFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * Return the array of subscribed events.
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array('request.error' => array('onRequestError', -1));
	}

	/**
	 * Convert generic Guzzle exceptions into Kinvey-specific exceptions.
	 *
	 * @return KinveyResponseException
	 */
	public function onRequestError(Event $event)
	{
		$e = $this->factory->fromResponse($event['request'], $event['response']);
		$event->stopPropagation();
		throw $e;
	}
}