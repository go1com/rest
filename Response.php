<?php

namespace go1\rest;

use Assert\LazyAssertionException;
use Exception;
use JsonException;
use Slim\Psr7\Stream;
use Slim\Psr7\Response as SlimResponse;
use function fopen;

class Response extends SlimResponse
{
    public function withJsonString($data, $status = null)
    {
		$body = new Stream(fopen('php://temp', 'r+'));
		$body->write($data);

		// Set the body and return the response with the proper headers
		$response = $this->withBody($body);

        return $status
            ? $response->withHeader('Content-Type', 'application/json')->withStatus($status)
            : $response->withHeader('Content-Type', 'application/json');
    }

	public function withJson($data, int $status = 200, int $encodingOptions = 0): self
	{
		// Encode the data to JSON
		$json = json_encode($data, $encodingOptions);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new JsonException(json_last_error_msg());
		}

		// Create a new body and write the JSON data to it
		$body = new Stream(fopen('php://temp', 'r+'));
		$body->write($json);

		// Return a new response with the JSON body and appropriate headers
		return $this->withBody($body)
			->withHeader('Content-Type', 'application/json')
			->withStatus($status);
	}

    public function jr($msg = 'Runtime error', int $statusCode = 400)
    {
        return $this->withJson(
            ['message' => ($msg instanceof Exception) ? $msg->getMessage() : $msg],
            $statusCode
        );
    }

    public function bodyString(): string
    {
        $this->getBody()->rewind();

        return $this->getBody()->getContents();
    }

    public function json(bool $assoc = false)
    {
        return json_decode($this->bodyString(), $assoc);
    }

    public function jr403($msg = 'Access denied.')
    {
        return $this->jr($msg, 403);
    }

    public function jr404($msg = 'Object not found.')
    {
        return $this->jr($msg, 404);
    }

    public function jr406($msg = 'Not acceptable.')
    {
        return $this->jr($msg, 406);
    }

    public function jr500($msg = 'Internal error.')
    {
        return $this->jr($msg, 500);
    }

    public function jrLazyAssertion(LazyAssertionException $e, int $statusCode = 400)
    {
        $data = ['message' => $e->getMessage()];

        foreach ($e->getErrorExceptions() as $error) {
            $data['error'][] = [
                'path'    => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $this->withJson($data, $statusCode);
    }
}
