<?php namespace GovTribe\LaravelKinvey\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;

class KinveyAuthPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * Inject authorization data into commands.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];

		// Check if this command requires authentication.
		if ($command->getOperation()->hasParam('appKey'))
		{
			// Inject the Kinvey app key.
			$command['appKey'] = $this->config['appKey'];

			// Based on the 'authMode', get the correct credentials.
			switch ($command['authMode'])
			{
				case 'user':
					$username = $command['username'];
					$password = $command['password'];
					$scheme = 'Basic';
					break;

				case 'session':
					$username = $command['token'];
					$password = null;
					$scheme = 'Kinvey';
					break;

				case 'app':
					$username = $this->config['appKey'];
					$password = $this->config['appSecret'];
					$scheme = 'Basic';
					break;

				case 'admin':
					$username = $this->config['appKey'];
					$password = $this->config['masterSecret'];
					$scheme = 'Basic';
					break;
			}

			// Inject the authorization header.
			$command['authHeader'] = $this->getAuthHeader($scheme, $username, $password);
		}
	}

	/**
	 * Get the authorization header value for a request.
	 *
	 * @param  string $username
	 * @param  string $password
	 * @return string
	 */
	protected static function getAuthHeader($scheme, $username, $password = null)
	{
		if (!$password) return $scheme . ' ' . $username;
		return $scheme . ' ' . base64_encode($username . ':' . $password);
	}
}
