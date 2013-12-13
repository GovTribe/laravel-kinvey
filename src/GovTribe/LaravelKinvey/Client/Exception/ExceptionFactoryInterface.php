<?php namespace GovTribe\LaravelKinvey\Client\Exception;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

/**
 * Interface used to create Kinvey exceptions
 */
interface ExceptionFactoryInterface
{
	/**
	 * Returns a Kinvey service specific exception
	 *
	 * @param RequestInterface $request  Unsuccessful request
	 * @param Response         $response Unsuccessful response that was encountered
	 *
	 * @return Exception
	 */
	public function fromResponse(RequestInterface $request, Response $response);
}