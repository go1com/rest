<?php

namespace go1\rest\wrapper\service;

use go1\rest\controller\ConsumeController;
use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\Stream;
use go1\rest\wrapper\Manifest;
use RuntimeException;
use function call_user_func;
use function call_user_func_array;
use function is_callable;
use function is_null;
use function is_string;
use function putenv;
use function sprintf;

class RestBuilder
{
    private $builder;
    private $config = [];
    private $boot;

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = ['boot' => $this->onBoot()];
    }

    private function onBoot()
    {
        return function (RestService $rest) {
            if (!is_null($this->boot)) {
                call_user_func($this->boot, $rest, $this->builder);
            }

            $binding = $this->builder->stream()->build();
            if ($binding) {
                $rest->get('/consume', [ConsumeController::class, 'get']);
                $rest->post('/consume', [ConsumeController::class, 'post']);
                $stream = $rest->getContainer()->get(Stream::class);
                foreach ($binding as $_) {
                    $stream->on($_[0], $_[1], $_[2]);
                }
            }

            $swagger = $this->builder->swagger();
            $paths = $swagger->getPaths();
            if (!$paths) {
                return;
            }

            $rest->get(
                '/swagger',
                function (Response $response) use ($swagger) {
                    return $response->withJson($swagger->build());
                }
            );

            foreach ($paths as $pattern => $methods) {
                foreach ($methods as $method => $_) {
                    $this->addRoute($rest, $method, $pattern, $_['#controller'], $_['#middleware'] ?? [], $_['parameters']);
                }
            }
        };
    }

    private function addRoute(RestService $rest, $method, $pattern, $controller, array $middleware, array $parameters)
    {
        $map = $rest->map([$method], $pattern, $controller);

        foreach ($middleware as $m) {
            if (is_callable($m)) {
                $map->add($m);
                continue;
            }

            if (is_string($m)) {
                if (!$rest->getContainer()->has($m)) {
                    throw new RuntimeException(sprintf('Invalid middleware: service %s not found.', $m));
                }

                $map->add($rest->getContainer()->get($m));
                continue;
            }

            throw new RuntimeException('Middleware must be a callable or name of service');
        }

        foreach ($parameters as $param) {
            if (isset($param['schema']['default'])) {
                $map->setArgument($param['name'], $param['schema']['default']);
            }
        }
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
