<?php

namespace go1\rest\wrapper\service;

use go1\rest\RestService;
use go1\rest\wrapper\ConfigBuilder;

class ServiceConfigBuilder
{
    private $builder;
    private $config = [];

    public function __construct(ConfigBuilder $builder)
    {
        $this->builder = $builder;
        $this->config = [];
    }

    public function withServiceName(string $name)
    {
        define('SERVICE_NAME', $name);

        $this
            ->builder
            ->dockerCompose()
            ->withEnv('SERVICE_TAGS', $name);

        return $this;
    }

    public function withVersion(string $version)
    {
        define('SERVICE_VERSION', $version);

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

    public function withBootCallback(callable $fn)
    {
        $this->config['boot'] = function (RestService $app) use ($fn) {
            call_user_func($fn, $app, $this);

            foreach ($this->builder->swagger()->getPaths() as $pattern => $methods) {
                foreach ($methods as $method => $_) {
                    $map = $app->map([$method], $pattern, $_['#controller']);

                    foreach ($_['parameters'] as $param) {
                        if (isset($param['schema']['default'])) {
                            $map->setArgument($param['name'], $param['schema']['default']);
                        }
                    }
                }
            }
        };

        return $this;
    }

    public function end(): ConfigBuilder
    {
        return $this->builder;
    }
}
