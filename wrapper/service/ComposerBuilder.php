<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\Manifest;

class ComposerBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = [];
    }

    public function withName(string $name)
    {
        $this->config['name'] = $name;

        return $this;
    }

    public function withPreferStable(bool $prefer)
    {
        $this->config['prefer-stable'] = $prefer;

        return $this;
    }

    public function withMinimumStability(string $value)
    {
        $this->config['minimum-stability'] = $value;

        return $this;
    }

    public function withPsr4Autoload(string $namespace, string $path)
    {
        $this->config['autoload']['psr-4'][$namespace] = $path;

        return $this;
    }

    public function require(string $name, string $version)
    {
        $this->config['require'][$name] = $version;

        return $this;
    }

    public function end(): Manifest
    {
        return $this->builder;
    }

    public function build()
    {
        return $this->config;
    }
}
