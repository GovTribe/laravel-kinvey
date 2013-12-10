<?php namespace GovTribe\LaravelKinvey\Eloquent;

use Carbon\Carbon;
use DateTime;
use MongoId;
use MongoDate;
use Guzzle\Service\Command\CommandInterface;
use GovTribe\LaravelKinvey\Facades\Kinvey;
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
	 * The name of the "deleted at" column.
	 *
	 * @var string
	 */
	const DELETED_AT = '_kmd.status.val';

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
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct(array $attributes = array())
	{
		$this->table = $this->collection;
		return parent::__construct($attributes);
	}

	/**
	 * Get the format for database stored dates.
	 *
	 * @return string
	 */
	protected function getDateFormat()
	{
		return 'c';
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
		//Kinvey needs all of the model's attributes for update operations (http://devcenter.kinvey.com/rest/guides/datastore#Saving).
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

	/**
	 * Restore a soft-deleted model instance.
	 *
	 * @return bool|null
	 */
	public function restore()
	{
		if ($this->softDelete)
		{
			// If the restoring event does not return false, we will proceed with this
			// restore operation. Otherwise, we bail out so the developer will stop
			// the restore totally. We will clear the deleted timestamp and save.
			if ($this->fireModelEvent('restoring') === false)
			{
				return false;
			}

			// Once we have saved the model, we will fire the "restored" event so this
			// developer will do anything they need to after a restore operation is
			// totally finished. Then we will return the result of the save call.
			$result = $this->save();

			$result = Kinvey::restore(array(
				'id' => $this->_id,
				'authMode' => 'admin',
			));

			$result = $result->getStatusCode() === 204 ? true : false;

			$this->fireModelEvent('restored', false);

			return $result;
		}
	}

	/**
	 * Get the fully qualified "deleted at" column.
	 *
	 * @return string
	 */
	public function getQualifiedDeletedAtColumn()
	{
		return $this->getDeletedAtColumn();
	}

	/**
	 * Get the "deleted at" attribute.
	 *
	 * @return string
	 */
	public function getDeletedAtAttribute()
	{
		$meta = $this->getAttribute('_kmd');

		if (isset($meta['status']['val']) && $meta['status']['val'] === 'disabled')
		{
			return $meta['status']['lastChange'];
		}
		else
		{
			return null;
		}
	}
}