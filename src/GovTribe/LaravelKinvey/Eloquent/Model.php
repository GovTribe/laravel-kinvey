<?php namespace GovTribe\LaravelKinvey\Eloquent;

use Carbon\Carbon;
use DateTime;
use MongoId;
use MongoDate;
use Guzzle\Service\Command\CommandInterface;
use GovTribe\LaravelKinvey\Eloquent\Builder as Builder;
use Illuminate\Database\Eloquent\Builder as LaravelBuilder;

abstract class Model extends \Illuminate\Database\Eloquent\Model {

	/**
	 * The database connection associated with the model.
	 *
	 * @var string
	 */
	protected $connection = 'kinvey';

	/**
	 * The collection associated with the model.
	 *
	 * @var string
	 */
	protected $collection;

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	/**
	 * Kinvey maintains timestamp data.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Create a new model instance from a command.
	 *
	 * @param CommandInterface
	 */
	public static function fromCommand(CommandInterface $command)
	{
		$model = new static;
		$model->setRawAttributes($command->getResponse()->json());
		return $model;
	}

	/**
	 * Get the format for database stored dates.
	 *
	 * @return string
	 */
	protected function getDateFormat()
	{
		return 'Y-m-d H:i:s';
		// d($this->getConnection()->getQueryGrammar());
		// die;

		// return $this->getConnection()->getQueryGrammar()->getDateFormat();
	}

	/**
	 * Get a new query builder instance for the connection.
	 *
	 * @return Builder
	 */
	protected function newBaseQueryBuilder()
	{
		return new Builder($this->getConnection());
	}

	/**
	 * Perform a model update operation.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @return bool
	 */
	protected function performUpdate(\Illuminate\Database\Eloquent\Builder $query)
	{
		//Kinvey needs all of the attributes, even on update operations.
		$dirty = $this->getAttributes();

		if (count($dirty) > 0)
		{
			// If the updating event returns false, we will cancel the update operation so
			// developers can hook Validation systems into their models and cancel this
			// operation if the model does not pass validation. Otherwise, we update.
			if ($this->fireModelEvent('updating') === false)
			{
				return false;
			}

			// Once we have run the update operation, we will fire the "updated" event for
			// this model instance. This will allow developers to hook into these after
			// models are updated, giving them a chance to do any special processing.
			$this->setKeysForSaveQuery($query)->update($dirty);

			$this->fireModelEvent('updated', false);
		}

		return true;
	}
}