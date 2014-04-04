<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use Guzzle\Service\Description\Parameter;
use Guzzle\Service\Exception\ValidationException;
use Illuminate\Support\Facades\Session;

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
		$client = $command->getClient();
		$config =  $client->getConfig();

		// Check if this command requires authentication.
		if ($command->getOperation()->hasParam('appKey'))
		{
			// Inject the Kinvey app key.
			$command['appKey'] = $config->get('appKey');

			// If the command is missing the 'authMode' key, get it from the client.
			if (!$command['authMode'] && !is_null($command->getClient()->getAuthMode()))
			{
				$command['authMode'] = $command->getClient()->getAuthMode();
			}

			if(!$command['authMode']) throw new ValidationException('authMode is required');
			if(!in_array($command['authMode'], $this->authModes)) throw new ValidationException('Invalid authMode : ' . $command['authMode']);

			// Based on the command's authMode', get the correct credentials.
			switch (true)
			{

				// Username and password
				case ($command['authMode'] === 'user'):

					if(!$command['username']) throw new ValidationException('username is required when using the user authMode');
					if(!$command['password']) throw new ValidationException('password is required when using the user authMode');

					$username = $command['username'];
					$password = $command['password'];
					$scheme = 'Basic';
					break;

				// Session tokens
				case ($token = Session::get('kinvey')):
				case ($token = $client->getAuthToken());
				case ($command['authMode'] === 'session'):

					$token = null;

					if ($command['token']) $token = $command['token'];
					if ($client->getAuthToken()) $token = $client->getAuthToken();
					if (Session::get('kinvey')) $token = Session::get('kinvey');

					if(!$token) throw new ValidationException('token is required when using the the session authMode');

					$username = $token;
					$password = null;
					$scheme = 'Kinvey';
					break;

				// App authentication and user sign-up
				case ($command->getOperation()->getName() === 'createEntity' && $command['collection'] === 'user'):
				case ($command['authMode'] === 'app'):

					$username = $config->get('appKey');
					$password = $config->get('appSecret');
					$scheme = 'Basic';
					break;

				// Admin mode
				case ($command['authMode'] === 'admin'):

					$username = $config->get('appKey');
					$password = $config->get('masterSecret');
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
