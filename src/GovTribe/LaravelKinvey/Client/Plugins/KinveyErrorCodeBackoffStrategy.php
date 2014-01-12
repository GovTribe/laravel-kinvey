<?php namespace GovTribe\LaravelKinvey\Client\Plugins;

use Guzzle\Plugin\Backoff\AbstractErrorCodeBackoffStrategy;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\HttpException;

class KinveyErrorCodeBackoffStrategy extends AbstractErrorCodeBackoffStrategy
{
	public function makesDecision()
	{
		return true;
	}

	protected function getDelay($retries, RequestInterface $request, Response $response = null, HttpException $e = null)
	{
		if ($response && isset($response->json()['error']))
		{
			return isset($this->errorCodes[$response->json()['error']]) ? true : null;
		}
	}
}