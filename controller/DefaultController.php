<?php

namespace go1\rest\controller;

use go1\rest\Response;
use go1\rest\RestService;
use function defined;
use function time;

class DefaultController
{
    public function get(Response $response)
    {
        return $response->withJson([
            'service' => defined('SERVICE_NAME') ? SERVICE_NAME : 'rest',
            'version' => defined('SERVICE_VERSION') ? SERVICE_VERSION : RestService::VERSION,
            'time'    => time(),
        ]);
    }
}
