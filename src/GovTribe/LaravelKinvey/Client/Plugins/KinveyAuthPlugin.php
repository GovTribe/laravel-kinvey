<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;

class KinveyAuthPlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
{

	/**
	 * Authentication modes
	 *
	 * @var array
	 */
	public $authModes = array('user', 'session', 'app', 'admin');

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

			if(!$command['authMode']) throw new ValidationException('authMode is required');
			if(!in_array($command['authMode'], $this->authModes)) throw new ValidationException('Invalid authMode : ' . $command['authMode']);

			// Based on the 'authMode', get the correct credentials.
			switch ($command['authMode'])
			{
				case 'user':
					if(!$command['username']) throw new ValidationException('username is required when using the user authMode');
					if(!$command['password']) throw new ValidationException('password is required when using the user authMode');

					$username = $command['username'];
					$password = $command['password'];
					$scheme = 'Basic';
					break;

				case 'session':
					if(!$command['token']) throw new ValidationException('token is required when using the user session');

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
