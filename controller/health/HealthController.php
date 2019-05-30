<?php

namespace go1\rest\controller\health;

use go1\rest\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;
use function array_map;

class HealthController
{
    private $dispatcher;

    public function __construct(EventDispatcher $dispatcher, HealthCollectorDefault $collector)
    {
        $this->dispatcher = $dispatcher;
        $this->dispatcher->addListener('rest.controller.health', [$collector, 'check']);
    }

    public function get(Response $response)
    {
        $event = new HealthCollectorEvent();
        $this
            ->dispatcher
            ->dispatch('rest.controller.health', $event);

        $statusCode = 200;
        $metrics = [];
        array_map(
            function ($health) use (&$metrics, &$statusCode) {
                list($name, $status, $error) = $health;
                $metrics[$name] = $status;

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
