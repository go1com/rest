<?php

namespace go1\rest\controller;

use DI\Container;
use go1\rest\Response;
use go1\rest\wrapper\Manifest;
use function array_values;
use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;
use function strpos;
use function strtolower;

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
            foreach ($api['paths'] as $uri => &$path) {
                foreach ($path as $method => &$resource) {
                    $this->buildResource($resource);

                    $correctMethod = strtolower($method);
                    if ($method != $correctMethod) {
                        $path[$correctMethod] = $resource;
                        unset($path[$method]);
                    }
                }
            }
        }

        return $api;
    }

    private function buildResource(array &$resource)
    {
        unset($resource['#middleware']);
        unset($resource['#controller']);

        if (isset($resource['parameters'])) {
            $resource['parameters'] = array_values($resource['parameters']);
        }

        if (!empty($resource['responses'])) {
            foreach ($resource['responses'] as $code => &$response) {
                $this->buildResponse($response);
            }
        }
    }

    private function buildResponse(array &$response)
    {
        if (!empty($response['content'])) {
            foreach ($response['content'] as $type => $content) {
                if (!empty($content['schema'])) {
                    $this->buildSchema($content['schema']);
                }
            }
        }
    }

    private function buildSchema(array &$schema)
    {
        foreach ($schema as $k => &$v) {
            if (is_array($v)) {
                $this->buildSchema($v);
            }
        }

        if (isset($schema['$ref']) && is_string($schema['$ref'])) {
            if (0 === strpos($schema['$ref'], 'file://')) {
                $ref = file_get_contents($schema['$ref']);
                $ref = json_decode($ref, true);
                $schema = $ref + $schema;
                unset($schema['$ref']);
            }
        }
    }
}
