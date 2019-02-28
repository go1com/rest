<?php

namespace go1\rest;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use stdClass;

class Stream
{
    private $listeners = [];

    public function on(string $eventName, callable $callable): self
    {
        $this->listeners[$eventName] = $callable;

        return $this;
    }

    public function commit(string $eventName, stdClass $payload)
    {
        foreach ($this->listeners as $name => $callable) {
            if ($eventName == $name) {
                call_user_func($callable, $this->resolveEventPayload($callable, $payload));
            }
        }
    }

    private function resolveEventPayload(callable $callable, stdClass $payload)
    {
        $reflection = is_array($callable) ?
            new ReflectionMethod($callable[0], $callable[1]) :
            new ReflectionFunction($callable);

        foreach ($reflection->getParameters() as $parameter) {
            $class = $parameter->getType()->getName();
            $reflection = new ReflectionClass($class);
            if ($reflection->hasMethod('create')) {
                return call_user_func([$class, 'create'], $payload);
            }
        }

        throw new RuntimeException('Un-supported event');
    }
}
