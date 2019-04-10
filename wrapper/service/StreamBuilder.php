<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\Manifest;
use function func_get_args;

class StreamBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = [];
    }

    public function on(string $routingKey, string $description, $callback)
    {
        $this->config[] = func_get_args();

        return $this;
    }

    public function build()
    {
        return $this->config;
    }

    public function endStream(): Manifest
    {
        return $this->builder;
    }
}
