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
	 * Get the fully qualified "deleted at" column.
	 *
	 * @return string
	 */
	public function getQualifiedDeletedAtColumn()
	{
		return $this->getDeletedAtColumn();
	}

	/**
	 * Run the increment or decrement method on the model.
	 *
	 * @param  string  $column
	 * @param  int     $amount
	 * @param  string  $method
	 * @return int
	 */
	protected function incrementOrDecrement($column, $amount, $method)
	{
		$attributes = $this->getAttributes();

		if (!isset($attributes[$column])) $attributes[$column] = 0;

		$attributes[$column] = $method === 'increment' ? $attributes[$column] + $amount : $attributes[$column] - $amount;

		$this->setRawAttributes($attributes);

		return $this->save();
	}

	/**
	 * Get the model's created_at attribute
	 *
	 * @return string
	 */
	public function getCreatedAtAttribute()
	{
		if (!isset($this->attributes['_kmd'])) return new Carbon;
		return new Carbon($this->attributes['_kmd']['ect']);
	}

	/**
	 * Get the model's updated_at attribute
	 *
	 * @return string
	 */
	public function getUpdatedAtAttribute()
	{
		if (!isset($this->attributes['_kmd'])) return new Carbon;
		return new Carbon($this->attributes['_kmd']['lmt']);
	}
}