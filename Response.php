<?php

namespace go1\rest;

use Assert\LazyAssertionException;
use Exception;

class Response extends \Slim\Http\Response
{
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
