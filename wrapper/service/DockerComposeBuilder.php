<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\ConfigBuilder;

class DockerComposeBuilder
{
    private $builder;
    private $config = [];

    public function __construct(ConfigBuilder $builder)
    {
        $this->builder = $builder;
        $this->config = [];
    }

    public function withEnv(string $name, string $value)
    {
        $this->config['environment'][] = "{$name}={$value}";

        return $this;
    }

    public function end(): ConfigBuilder
    {
        return $this->builder;
    }
}
