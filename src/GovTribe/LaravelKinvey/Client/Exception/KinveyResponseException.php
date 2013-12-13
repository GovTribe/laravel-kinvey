<?php namespace GovTribe\LaravelKinvey\Client\Exception;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\RequestException;

class KinveyResponseException extends RequestException
{
	/**
	 * @var Response Response
	 */
	protected $response;

	/**
	 * @var RequestInterface Request
	 */
	protected $request;

	/**
	 * @var string Request ID
	 */
	protected $requestId;

	/**
	 * @var string Exception type (client / server)
	 */
	protected $exceptionType;

	/**
	 * @var string Debug information returned by Kinvey
	 */
	protected $debug;

	/**
	 * Set the debug information returned from Kinvey
	 *
	 * @param string $debug
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;
	}

	/**
	 * Get the debug information
	 *
	 * @return string|null
	 */
	public function getDebug()
	{
		return $this->debug;
	}

	/**
	 * Set the exception type
	 *
	 * @param string $type Exception type
	 */
	public function setExceptionType($type)
	{
		 $this->exceptionType = $type;
	}

	/**
	 * Get the exception type
	 *
	 * @return string|null
	 */
	public function getExceptionType()
	{
		return $this->exceptionType;
	}

	/**
	 * Set the request ID
	 *
	 * @param string $id Request ID
	 */
	public function setRequestId($id)
	{
		$this->requestId = $id;
	}

	/**
	 * Get the Request ID
	 *
	 * @return string|null
	 */
	public function getRequestId()
	{
		return $this->requestId;
	}

	/**
	 * Set the associated response
	 *
	 * @param Response $response Response
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
	}

	/**
	 * Get the associated response object
	 *
	 * @return Response|null
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Set the associated request
	 *
	 * @param RequestInterface $request
	 */
	public function setRequest(RequestInterface $request)
	{
		$this->request = $request;
	}

	/**
	 * Get the associated request object
	 *
	 * @return RequestInterface|null
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Get the status code of the response
	 *
	 * @return int|null
	 */
	public function getStatusCode()
	{
		return $this->response ? $this->response->getStatusCode() : null;
	}
}
