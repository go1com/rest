<?php

namespace go1\rest\controller\health;

class HealthCollectorEvent
{
    private $metrics;

    public function set(string $metric, $key, bool $error)
    {
        $this->metrics[] = [$metric, $key, $error];
    }

    public function get()
    {
        return $this->metrics;
    }
}
