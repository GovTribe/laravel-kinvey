<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Guzzle\Common\Event;
use GovTribe\LaravelKinvey\Database\Eloquent\Model;

class KinveyUserSoftDeletePlugin extends KinveyGuzzlePlugin implements EventSubscriberInterface
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
	 * Map Laravel's soft delete action to Kinvey's user suspend
	 * endpoint.
	 *
	 * @param  Guzzle\Common\Event
	 * @return void
	 */
	public function beforePrepare(Event $event)
	{
		$command = $event['command'];
		$operation = $command->getOperation();
		$client = $command->getClient();

		if ($command->getName() !== 'updateEntity') return;
		if ($operation->getParam('collection')->getDefault() !== 'user') return;

		// Attempt to get the model's deleted at key from the array of values passed
		// to the command.
		$statusValue = false;

		if ($command->offsetExists(Model::DELETED_AT))
		{
			$statusValue = $command->offsetGet(Model::DELETED_AT);
		}
		elseif ($command->offsetExists('_kmd'))
		{
			$_kmd = array_dot($command->offsetGet('_kmd'));

			$statusField = MODEL::DELETED_AT;
			$statusField = str_replace('_kmd.', '', MODEL::DELETED_AT);

			if (array_key_exists($statusField, $_kmd))
			{
				$statusValue = $_kmd[$statusField];
			}
		}

		// Could not determine the status field.
		if ($statusValue === false)
		{
			return;
		}
		// Suspend the user.
		elseif (!is_null($statusValue))
		{
			$event->stopPropagation();

			$suspendCommand = $client->getCommand('deleteEntity', array(
				'collection' => 'user',
				'_id' => $command->offsetGet('_id'),
			));
			$suspendCommand->execute();
		}
		// Restore.
		else
		{
			$event->stopPropagation();

			$restoreCommand = $client->getCommand('restore', array(
				'_id' => $command->offsetGet('_id'),
			));
			$restoreCommand->execute();
		}
	}
}
