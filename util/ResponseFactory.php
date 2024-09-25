<?php

namespace go1\rest\util;

use Fig\Http\Message\StatusCodeInterface;
use go1\rest\Response;
use Slim\Psr7\Factory\ResponseFactory as Psr7ResponseFactory;

class ResponseFactory extends Psr7ResponseFactory
{
    public function createResponse(
        int $code = StatusCodeInterface::STATUS_OK,
        string $reasonPhrase = ''
    ): Response {
        $res = new Response($code);

        if ($reasonPhrase !== '') {
            $res = $res->withStatus($code, $reasonPhrase);
        }

        return $res;
    }
}
