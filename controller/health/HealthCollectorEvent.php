<?php

namespace go1\rest\controller\health;

use Symfony\Component\EventDispatcher\Event;

class HealthCollectorEvent extends Event
{
    private $metrics;

    public function set(string $name, $status, bool $error)
    {
        $this->metrics = [$name, $status, $error];
    }

    public function get()
    {
        return $this->metrics;
    }
}
