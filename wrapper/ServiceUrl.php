<?php

namespace go1\rest\wrapper;

use DI\Container;
use function ltrim;
use function rtrim;

class ServiceUrl
{
    private $container;
    private $pattern;
    private $env;

    public function __construct(Container $c)
    {
        $this->container = $c;

        $this->pattern = $this->container->has('servicePattern')
            ? $this->container->get('servicePattern')
            : (getenv('SERVICE_URL_PATTERN') ?: 'http://SERVICE.ENVIRONMENT.go1.service');

        $this->env = $this->container->has('env') ? $this->container->get('env') : 'dev';
    }

    public function get(string $serviceName, string $path = ''): string
    {
        $url = str_replace(['SERVICE', 'ENVIRONMENT'], [$serviceName, $this->env], $this->pattern);
        if (!$path) {
            return $url;

        }

        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }
}
