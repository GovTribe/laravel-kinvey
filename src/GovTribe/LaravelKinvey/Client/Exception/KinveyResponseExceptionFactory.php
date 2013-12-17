<?php namespace GovTribe\LaravelKinvey\Client\Exception;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class KinveyResponseExceptionFactory implements ExceptionFactoryInterface
{
	/**
	 * Returns a Kinvey service specific exception
	 *
	 * @param RequestInterface $request  Unsuccessful request
	 * @param Response         $response Unsuccessful response that was encountered
	 *
	 * @return Exception
	 */
	public function fromResponse(RequestInterface $request, Response $response)
	{
		$message = $response->json();

		$parts = array(
			'type' => isset($message['error']) ? $message['error'] : null,
			'description' => isset($message['description']) ? $message['description'] : null,
			'request_id' => $response->getHeaders()->get('x-kinvey-request-id'),
			'debug' => isset($message['debug']) && !empty($message['debug']) ? $message['debug'] : null,
		);

		return $this->createException($request, $response, $parts);
	}

	/**
	 * Create an prepare an exception object
	 *
	 * @param RequestInterface $request   Request
	 * @param Response         $response  Response received
	 * @param array            $parts     Parsed exception data
	 *
	 * @return \Exception
	 */
	protected function createException(RequestInterface $request, Response $response, array $parts)
	{
		$message = 'Status Code: ' . $response->getStatusCode() . PHP_EOL
			. 'Kinvey Request ID: ' . $parts['request_id'] . PHP_EOL
			. 'Kinvey Exception Type: ' . $parts['type'] . PHP_EOL
			. 'Kinvey Error Message: ' . $parts['description'] . PHP_EOL
			. 'Kinvey Debug: ' . $parts['debug'];

		$class = new KinveyResponseException($message);
		$class->setExceptionType($parts['type']);
		$class->setResponse($response);
		$class->setRequest($request);
		$class->setRequestId($parts['request_id']);
		$class->setDebug($parts['debug']);
		return $class;
	}
}