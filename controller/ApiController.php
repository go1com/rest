<?php

namespace go1\rest\controller;

use DI\Container;
use go1\rest\Response;
use go1\rest\wrapper\Manifest;

class ApiController
{
    /** @var Manifest */
    private $manifest;

    public function __construct(Container $container)
    {
        $this->manifest = require $container->get('REST_MANIFEST');
    }

    public function get(Response $response)
    {
        return $response->withJson($this->build());
    }

    private function build()
    {
        $api = $this->manifest->openAPI()->build();

        unset($api['middlewares']);

        if (!empty($api['paths'])) {
            foreach ($api['paths'] as &$path) {
                foreach ($path as &$resource) {
                    unset($resource['#middleware']);
                    unset($resource['#controller']);
                }
            }
        }

        return $api;
    }
}
