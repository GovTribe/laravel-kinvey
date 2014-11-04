<?php namespace GovTribe\LaravelKinvey\Database\Eloquent;

use GovTribe\LaravelKinvey\Database\Connection;
use GovTribe\LaravelKinvey\Client\KinveyClient;
use GovTribe\LaravelKinvey\Client\Exception\KinveyResponseException;
use Jenssegers\Mongodb\Query\Builder as MongoBuilder;
use DateTime;
use Config;
use Auth;

class Builder extends MongoBuilder
{
	/**
	 * Create a new query builder instance.
	 *
	 * @param Connection $connection
	 * @return void
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Convert a key to MongoID if needed.
	 *
	 * @param  mixed $id
	 * @return mixed
	 */
	public function convertKey($id)
	{
		// As Kinvey doesn't use MongoIDs, just return the string id.
		if ($id instanceof \MongoId) $id = $id->__toString();
		return $id;
	}

	/**
	 * Insert a new record and get the value of the primary key.
	 *
	 * @param  array   $values
	 * @param  string  $sequence
	 * @return int
	 */
	public function insertGetId(array $values, $sequence = null)
	{
		$result = $this->collection->insert($values);
		return $result['_id'];
	}

	/**
	 * Insert a new record into the database.
	 *
	 * @param  array  $values
	 * @return array
	 */
	public function insert(array $values)
	{
		$start = microtime(true);

		// Since every insert gets treated like a batch insert, we will have to detect
		// if the user is inserting a single document or an array of documents.
		$batch = true;
		foreach ($values as $value)
		{
		    // As soon as we find a value that is not an array we assume the user is
		    // inserting a single document.
		    if (!is_array($value))
		    {
		        $batch = false; break;
		    }
		}

		if (!$batch) $values = array($values);

		// Batch insert
		$result = array();

		foreach ($values as $insert)
		{
			$result[] = $this->collection->insert($insert);
		}

		return $result;
	}

	/**
	 * Update a record in the database.
	 *
	 * @param  array  $values
	 * @param  array  $options
	 * @return int
	 */
	public function update(array $values, array $options = array())
	{
		return $this->performUpdate($values, $options);
	}

	/**
	 * Set the collection used by this query.
	 *
	 * @param  string  $collection
	 * @return void
	 */
	public function setCollection(KinveyClient $collection)
	{
		// We'll treat the KinveyClient instance as the Mongo 'collection'.
		$this->collection = $collection;
	}

	/**
	 * Get the collection used by this query.
	 *
	 * @return KinveyClient
	 */
	public function getCollection()
	{
		return $this->collection;
	}

	/**
	 * Perform an update query.
	 *
	 * @param  array  $query
	 * @param  array  $options
	 * @param  array  $response
	 * @return int
	 */
    protected function performUpdate($query, array $options = array(), array $response = array())
	{
		$wheres = $this->compileWheres();

		//Update one.
		if (isset($query['_id']))
		{
			$response = $this->collection->update($this->formatQuery($query));

			// If this is a user, and they've changed their password, trigger a new login event.
			if (isset($response['_kmd']['authtoken']))
			{
				$currentUser = Auth::user();
				$_kmd = $currentUser->_kmd;
				$_kmd['authtoken'] = $response['_kmd']['authtoken'];
				$currentUser->_kmd = $_kmd;
				Auth::login($currentUser);
			}
		}
		//Update many.
		else
		{
			foreach ($this->get() as $entity)
			{
				$response[] = $this->collection->update(array_merge($entity, $this->formatQuery($query)));
			}
		}

		return count($response);
	}

	/**
	 * Delete a record from the database.
	 *
	 * @param  mixed  $id
	 * @return int
	 */
	public function delete($id = null)
	{
		$wheres = $this->compileWheres();

		foreach ($this->get() as $entity)
		{
			$this->collection->remove($entity);
		}
	}

	/**
	 * Execute the query as a fresh "select" statement.
	 *
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function getFresh($columns = array('*'))
	{
		// If no columns have been specified for the select statement, we will set them
		// here to either the passed columns, or the standard default of retrieving
		// all of the columns on the table using the "wildcard" column character.
		if (is_null($this->columns))
		{
			$this->columns = $columns;
		}

		// Drop all columns if * is present, MongoDB does not work this way
		if (in_array('*', $this->columns))
		{
			$this->columns = array();
		}

		// Compile wheres
		$wheres = $this->compileWheres();

		// Aggregation query.
		if (isset($this->aggregate['function']))
		{
			switch ($this->aggregate['function'])
			{
				case 'count':
					$result = $this->collection->group(array(
						'key' => array('_id' => true),
						'initial' => array('count' => 0),
						'reduce' => "function(doc,out){ out.count++;}",
						'condition' => $wheres,

					));

					$result = count($result);
					break;

				case 'max':
				case 'min':
					$result = $this->collection->query(array(
						'query' => $wheres,
						'fields' => $this->columns,
						'sort' => array($this->columns[0] => $this->aggregate['function'] === 'max' ? -1 : 1),
						'limit' => 1,
					));
					$result = empty($result) ? 0 : $result[0][$this->columns[0]];
					break;

				default:
					$result = array();
					break;
			}

			return array(
				array(
					'aggregate' => $result,
				),
			);
		}
		// Normal query
		else
		{
			// Execute query.
			return $this->collection->query(array(
				'query' => $wheres,
				'fields' => $this->columns,
				'sort' => $this->orders,
				'skip' => $this->offset,
				'limit' => $this->limit,
			));
		}
	}

	/**
	 * Execute an aggregate function on the database.
	 *
	 * @param  string  $function
	 * @param  array   $columns
	 * @return mixed
	 */
	public function aggregate($function, $columns = array('*'))
	{
		$this->aggregate = compact('function', 'columns');

		$results = $this->get($columns);

		// Once we have executed the query, we will reset the aggregate property so
		// that more select queries can be executed against the database without
		// the aggregate value getting in the way when the grammar builds it.
		$this->columns = null; $this->aggregate = null;

		if (isset($results[0]))
		{
			$result = (array) $results[0];

			return $result['aggregate'];
		}
	}

	/**
	 * Get a new instance of the query builder.
	 *
	 * @return Builder
	 */
	public function newQuery()
	{
		return new Builder($this->connection);
	}

	/**
	 * Run a truncate statement on the table.
	 *
	 * @return int
	 */
	public function truncate()
	{
		try
		{
			$result = $this->collection->deleteCollection();
		}
		catch (KinveyResponseException $e)
		{
			if ($e->getStatusCode() === 404)
			{
				$result['count'] = 0;
			}
			else
			{
				throw $e;
			}
		}

		return $result['count'];
	}

 	/**
     * Compile the query's where clauses.
     *
     * @param  array  $where
     * @return array
     */
	protected function compileWhereBasic($where)
	{
		extract($where);

		// Replace like with MongoRegex
		if ($operator == 'like') {
			$operator = '=';
			$regex    = str_replace('%', '', $value);

			// Prepare regex
			if (substr($value, 0, 1) != '%')
				$regex = '^' . $regex;
			if (substr($value, -1) != '%')
				$regex = $regex . '$';

			$value = array('$regex' => "$regex");
		}

		if (!isset($operator) || $operator == '=')
		{
			$query = array(
				$column => $value
			);
		}
		elseif (array_key_exists($operator, $this->conversion))
		{
			$query = array(
				$column => array(
					$this->conversion[$operator] => $value
				)
			);
		}
		else
		{
			$query = array(
				$column => array(
					'$' . $operator => $value
				)
			);
		}

		return $query;
	}

	/**
	 * Format a query.
	 *
	 * @param  array  $data
	 * @param  array  $formatted
	 * @return array
	 */
	public function formatQuery(array $data, array $formatted = array())
	{
		// Convert dot notation to multidimensional array elements.
		foreach ($data as $key => $value)
		{
			array_set($formatted, $key, $value);
		}
		return $formatted;
	}

	/**
	* Compile the where array.
	*
	* @return array
	*/
	protected function compileWheres()
	{
		if (!$this->wheres) return array();

		// The new list of compiled wheres
		$wheres = array();

		foreach ($this->wheres as $i => &$where)
		{
			// Convert id's
			if (isset($where['column']) && $where['column'] == '_id')
			{
				// Multiple values
				if (isset($where['values']))
				{
					foreach ($where['values'] as &$value)
					{
						$value = $this->convertKey($value);
					}
				}
				// Single value
				elseif (isset($where['value']))
				{
					$where['value'] = $this->convertKey($where['value']);
				}
			}

			// Convert dates
			if (isset($where['value']) && $where['value'] instanceof DateTime)
			{
				$where['value'] = $where['value']->format(DateTime::ISO8601);
			}

			// First item of chain
			if ($i == 0 && count($this->wheres) > 1 && $where['boolean'] == 'and')
			{
				// Copy over boolean value of next item in chain
				$where['boolean'] = $this->wheres[$i+1]['boolean'];
			}

			// Delegate
			$method = "compileWhere{$where['type']}";
			$compiled = $this->{$method}($where);

			// Check for or
			if ($where['boolean'] == 'or')
			{
				$compiled = array('$or' => array($compiled));
			}

			// Merge compiled where
			$wheres = array_merge_recursive($wheres, $compiled);
		}

		return $wheres;
	}
}
