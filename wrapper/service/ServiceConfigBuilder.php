<?php

namespace go1\rest\wrapper\service;

use go1\rest\RestService;
use go1\rest\wrapper\Manifest;
use RuntimeException;

class ServiceConfigBuilder
{
    private $builder;
    private $config = [];
    private $boot;

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = [
            'boot' => function (RestService $app) {
                if (!is_null($this->boot)) {
                    call_user_func($this->boot, $app, $this->builder);
                }

                foreach ($this->builder->swagger()->getPaths() as $pattern => $methods) {
                    foreach ($methods as $method => $_) {
                        $map = $app->map([$method], $pattern, $_['#controller']);

                        if (!empty($_['#middleware'])) {
                            foreach ($_['#middleware'] as $m) {
                                if (is_callable($m)) {
                                    $map->add($m);
                                    continue;
                                }

                                if (is_string($m)) {
                                    if (!$app->getContainer()->has($m)) {
                                        throw new RuntimeException(sprintf('Invalid middleware: service %s not found.', $m));
                                    }
                                    $map->add($app->getContainer()->get($m));
                                    continue;
                                }

                                throw new RuntimeException('Middleware must be a callable or name of service');
                            }
                        }

                        foreach ($_['parameters'] as $param) {
                            if (isset($param['schema']['default'])) {
                                $map->setArgument($param['name'], $param['schema']['default']);
                            }
                        }
                    }
                }
            },
        ];
    }

    public function set($k, $v)
    {
        $this->config[$k] = $v;

        return $this;
    }

    public function withServiceName(string $name)
    {
        defined('SERVICE_NAME') || define('SERVICE_NAME', $name);

        $this
            ->builder
            ->dockerCompose()
            ->withEnv('SERVICE_TAGS', $name);

        return $this;
    }

    public function withVersion(string $version)
    {
        defined('SERVICE_VERSION') || define('SERVICE_VERSION', $version);

        return $this;
    }

    /**
     * @param string       $name
     * @param string|array $option
     * @return $this
     */
    public function withEsOption(string $name, $option)
    {
        $this->config['esOptions'][$name] = is_array($option) ? $option : ['endpoint' => $option];

        return $this;
    }

    public function withConfigFile(string $path)
    {
        if (file_exists($path)) {
            $_ = require $path;
            foreach ($_ as $k => $v) {
                $this->set($k, $v);
            }
        }

        return $this;
    }

    public function withBootCallback(callable $boot)
    {
        $this->boot = $boot;

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

    public function get()
    {
        return new RestService($this->build());
    }
}
