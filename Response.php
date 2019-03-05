<?php

namespace go1\rest;

use Assert\LazyAssertionException;
use Exception;

class Response extends \Slim\Http\Response
{
    public function jr($msg, int $statusCode = 400)
    {
        return $this->withJson(
            ['message' => ($msg instanceof Exception) ? $msg->getMessage() : $msg],
            $statusCode
        );
    }

    public function json()
    {
        return json_decode($this->getBody()->getContents());
    }

    public function jr403($msg)
    {
        return $this->jr($msg, 403);
    }

    public function jr404($msg)
    {
        return $this->jr($msg, 404);
    }

    public function jr406($msg)
    {
        return $this->jr($msg, 406);
    }

    public function jr500($msg)
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
