<?php

namespace go1\rest\wrapper\service;

use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\wrapper\Manifest;
use RuntimeException;
use function putenv;

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

                $swagger = $this->builder->swagger();
                $paths = $swagger->getPaths();
                if (!$paths) {
                    return;
                }

                $app->get('/swagger', function (Response $response) use ($swagger) {
                    return $response->withJson($swagger->build());
                });

                foreach ($paths as $pattern => $methods) {
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
        putenv('REST_SERVICE_NAME=' . $name);

        $this
            ->builder
            ->dockerCompose()
            ->withEnv('SERVICE_80_NAME', $name);

        return $this;
    }

    public function withVersion(string $version)
    {
        putenv('REST_SERVICE_VERSION=' . $version);

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

    public function endRest(): Manifest
    {
        return $this->builder;
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
