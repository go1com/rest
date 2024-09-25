<?php

namespace go1\rest\controller\health;

use go1\rest\Request;
use go1\rest\Response;
use function array_map;

class HealthController
{
    private $collector;

    public function __construct(HealthCollectorDefault $collector)
    {
        $this->collector = $collector;
    }

    public function get(Request $request, Response $response)
    {
        $event = new HealthCollectorEvent();
        $this->collector->check($event);

        $statusCode = 200;
        $metrics = [];
        array_map(
            function ($health) use (&$metrics, &$statusCode) {
                list($metric, $key, $error) = $health;
                $metrics[$metric][$key] = $error ? false : true;

                if ($error) {
                    $statusCode = 500;
                }
            },
            $event->get()
        );

        return $response
            ->withStatus($statusCode)
            ->withJson($metrics);
    }
}
