<?php

namespace go1\rest\wrapper\service;

use go1\rest\controller\InstallController;
use go1\rest\controller\MessageListenerController;
use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\Stream;
use go1\rest\wrapper\Manifest;
use RuntimeException;
use function call_user_func;
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
            $swagger = $this->builder->swagger();

            if (!is_null($this->boot)) {
                call_user_func($this->boot, $rest, $this->builder);
            }

            $binding = $this->builder->stream()->build();
            if ($binding) {
                $rest->get('/consume', [MessageListenerController::class, 'get']);
                $rest->post('/consume', [MessageListenerController::class, 'post']);
                $stream = $rest->getContainer()->get(Stream::class);
                foreach ($binding as $_) {
                    $stream->on($_[0], $_[1], $_[2]);
                }
            }

            if ($this->has('restDbSchema')) {
                $swagger->withPath('/install', 'POST', [InstallController::class, 'post']);
            }

            $paths = $swagger->getPaths();
            if (!$paths) {
                return;
            }

            $rest->get(
                '/api',
                function (Response $response) use ($swagger) {
                    return $response->withJson($swagger->build());
                }
            );

            foreach ($paths as $pattern => $methods) {
                foreach ($methods as $method => $_) {
                    $this->addRoute($rest, $method, $pattern, $_['#controller'], $_['#middleware'] ?? [], $_['parameters']);
                }
            }

            $middlewares = $swagger->getMiddlewares();
            foreach ($middlewares as $m) {
                $rest->add($this->parseMiddleware($rest, $m));
            }
        };
    }

    private function addRoute(RestService $rest, $method, $pattern, $controller, array $middleware, array $parameters)
    {
        $map = $rest->map([$method], $pattern, $controller);

        foreach ($middleware as $m) {
            $map->add($this->parseMiddleware($rest, $m));
        }

        foreach ($parameters as $param) {
            if (isset($param['schema']['default'])) {
                $map->setArgument($param['name'], $param['schema']['default']);
            }
        }
    }

    private function parseMiddleware(RestService $rest, $m)
    {
        if (is_callable($m)) {
            return $m;
        }

        if (is_string($m)) {
            if (!$rest->getContainer()->has($m)) {
                throw new RuntimeException(sprintf('Invalid middleware: service %s not found.', $m));
            }

            return $rest->getContainer()->get($m);
        }

        throw new RuntimeException('Middleware must be a callable or name of service');
    }

    public function has(string $k): bool
    {
        return isset($this->config[$k]);
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

        $this->config['name'] = $name;

        return $this;
    }

    public function withVersion(string $version)
    {
        putenv('REST_SERVICE_VERSION=' . $version);

        return $this;
    }

    public function withDatabaseSchema(string $dbConnectionClass, string $dbSchemaClass)
    {
        return $this->set('restDbSchema', [$dbConnectionClass, $dbSchemaClass]);
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
