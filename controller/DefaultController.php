<?php

namespace go1\rest\controller;

use go1\rest\Response;
use go1\rest\RestService;
use function getenv;
use function time;

class DefaultController
{
    public function get(Response $response)
    {
        return $response->withJson([
            'service' => getenv('REST_SERVICE_NAME') ?: 'rest',
            'version' => getenv('REST_SERVICE_VERSION') ?: RestService::VERSION,
            'time'    => time(),
            'tag'     => getenv('DD_VERSION') ?: '',
            'env'     => getenv('DD_ENV') ?: ''
        ]);
    }
}
