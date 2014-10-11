<?php namespace GovTribe\LaravelKinvey\Database\Eloquent;

use DateTime;
use GovTribe\LaravelKinvey\Database\Eloquent\Builder as Builder;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;

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
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = true;

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
		return DateTime::ISO8601;
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
	 * Get the attributes that have been changed since last sync.
	 *
	 * @return array
	 */
	public function getDirty()
	{
		// Kinvey needs all of the model's attributes for update operations
		// see (http://devcenter.kinvey.com/rest/guides/datastore#Saving).

		$dirty = $this->attributes;

		if (array_key_exists(static::UPDATED_AT, $dirty)) unset($dirty[static::UPDATED_AT]);

		return $dirty;
	}

	/**
	 * Insert the given attributes and set the ID on the model.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder  $query
	 * @param  array  $attributes
	 * @return void
	 */
	protected function insertAndSetId(BaseBuilder $query, $attributes)
	{
		// This allows values that are set by Kinvey, and returned in the
		// inserted model ('_kmd', '_acl' etc.) to be immediately added
		// to the new model instance.

		$result = $query->insert($attributes);

		$this->setRawAttributes(reset($result));
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
	 * Get the table qualified key name.
	 *
	 * @return string
	 */
	public function getQualifiedKeyName()
	{
		return $this->getKeyName();
	}
}