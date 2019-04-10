<?php

namespace go1\rest;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use RuntimeException;
use function call_user_func;
use function is_string;

class Stream
{
    private $container;
    private $listeners = [];
    private $transports;

    public function __construct(ContainerInterface $c, ?callable $transport = null)
    {
        $this->container = $c;
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
        foreach ($this->listeners as $name => &$listener) {
            if ($eventName == $name) {
                if (is_array($listener['fn'])) {
                    if (isset($listener['fn'][0])) {
                        if (is_string($listener['fn'][0])) {
                            $listener['fn'][0] = $this->container->get($listener['fn'][0]);
                        }
                    }
                }

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
            $type = $parameter->getType();
            if (!$type) {
                return $payload;
            }

            switch ($type->getName()) {
                case 'string':
                case 'int':
                case 'float':
                case 'bool':
                case 'stdClass':
                    return $payload;

                default:
                    $reflection = new ReflectionClass($type->getName());
                    if ($reflection->hasMethod('create')) {
                        return call_user_func([$type->getName(), 'create'], $payload);
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
