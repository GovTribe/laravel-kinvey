<?php namespace GovTribe\LaravelKinvey\Database;

use Guzzle\Service\Command\OperationCommand;
use GovTribe\LaravelKinvey\Client\KinveyClient;
use GovTribe\LaravelKinvey\Database\Eloquent\Builder;

class Connection extends \Illuminate\Database\Connection
{
	/**
	 * The MongoClient connection handler.
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * The Kinvey client.
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * Create a new database connection instance.
	 *
	 * @param  KinveyClient $kinvey
	 * @return void
	 */
	public function __construct(KinveyClient $kinvey)
	{
		$this->db = $kinvey;
	}

	/**
	 * Access the raw Kinvey client.
	 *
	 * @return KinveyClient
	 */
	public function getKinvey()
	{
		return $this->db;
	}

	/**
	 * Get a MongoDB collection.
	 *
	 * @param  string   $name
	 * @return string
	 */
	public function getCollection($name)
	{
		$instance = clone $this->db;

		$instance->setCollectionName($name);

		return $instance;
	}

	/**
	 * Begin a fluent query against a database collection.
	 *
	 * @param  string  $collection
	 * @return QueryBuilder
	 */
	public function collection($collection)
	{
		$query = new Builder($this);
		return $query->from($collection);
	}

	/**
	 * Log a query in the connection's query log.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @param  $time
	 * @return void
	 */
	public function logQuery($query, $bindings, $time = null)
	{

	}

	/**
	 * Dynamically pass methods to the connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array($this->db, $method), $parameters);
	}
}