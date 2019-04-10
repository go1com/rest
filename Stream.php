<?php

namespace go1\rest;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use function call_user_func;

class Stream
{
    private $listeners = [];
    private $transports;

    public function __construct(?callable $transport = null)
    {
        $this->addTransport([$this, 'defaultTransport']);
        if (null != $transport) {
            $this->addTransport($transport);
        }
    }

    public function addTransport(callable $transport)
    {
        $this->transports[] = $transport;
    }

    public function on(string $eventName, string $description, $callable): self
    {
        $this->listeners[$eventName]['description'] = $description;
        $this->listeners[$eventName]['fn'] = $callable;

        return $this;
    }

    public function commit(string $eventName, string $payload, array $context = [])
    {
        foreach ($this->transports as $transport) {
            call_user_func($transport, $eventName, $payload, $context);
        }
    }

    protected function defaultTransport($eventName, string $payload)
    {
        foreach ($this->listeners as $name => $listener) {
            if ($eventName == $name) {
                call_user_func($listener['fn'], $this->resolveEventPayload($listener['fn'], $payload));
            }
        }
    }

    private function resolveEventPayload(callable $callable, string $payload)
    {
        $reflection = is_array($callable) ?
            new ReflectionMethod($callable[0], $callable[1]) :
            new ReflectionFunction($callable);

        if (!$parameters = $reflection->getParameters()) {
            return null; # no params to be resolved
        }

        foreach ($parameters as $parameter) {
            $class = $parameter->getType()->getName();

            switch ($class) {
                case 'string':
                case 'int':
                case 'float':
                case 'bool':
                    return $payload;

                default:
                    $reflection = new ReflectionClass($class);
                    if ($reflection->hasMethod('create')) {
                        return call_user_func([$class, 'create'], $payload);
                    }
            }
        }

        throw new RuntimeException('Un-supported event');
    }

    public function listeners(): array
    {
        return $this->listeners ?: [];
    }
}
