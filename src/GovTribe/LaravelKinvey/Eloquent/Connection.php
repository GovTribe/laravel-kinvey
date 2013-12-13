<?php
namespace GovTribe\LaravelKinvey\Eloquent;

use GovTribe\LaravelKinvey\Client\KinveyClient;
use Guzzle\Service\Command\OperationCommand;

class Connection extends \Illuminate\Database\Connection
{

	/**
	 * The MongoDB database handler.
	 *
	 * @var resource
	 */
	protected $db;

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
	protected $kinvey;

	/**
	 * Create a new database connection instance.
	 *
	 * @param  KinveyClient $kinvey
	 * @return void
	 */
	public function __construct(KinveyClient $kinvey)
	{
		$this->kinvey = $kinvey;
		$this->disableQueryLog();
	}

	/**
	 * Access the raw Kinvey client.
	 *
	 * @return KinveyClient
	 */
	public function getKinvey()
	{
		return $this->kinvey;
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
	 * Dynamically pass methods to the connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array(array(
			$this->kinvey,
			$method,
		), $parameters);
	}
}