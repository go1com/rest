<?php

namespace go1\rest\wrapper\service\open_api;

use go1\rest\wrapper\Manifest;

class OpenApiBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = ['paths' => [], 'middlewares' => []];
    }

    public function withOpenAPI(string $version)
    {
        $this->config['openapi'] = $version;

        return $this;
    }

    public function withServer(string $url, string $description)
    {
        $this->config['server'][] = [
            'url'         => $url,
            'description' => $description,
        ];

        return $this;
    }

    public function withPath(string $path, string $method, $controller = null): PathBuilder
    {
        $this->config['paths'][$path][$method] = [];
        $pathBuilder = new PathBuilder($this, $this->config['paths'][$path][$method]);

        if ($controller) {
            $pathBuilder->withController($controller);
        }

        return $pathBuilder;
    }

    public function withMiddleware($middleware): self
    {
        $this->config['middlewares'][] = $middleware;

        return $this;
    }

    public function getPaths()
    {
        return $this->config['paths'];
    }

    public function getMiddlewares()
    {
        return $this->config['middlewares'];
    }

    /**
     * withTag($name, $description)
     *
     * withExternalDocs($url, $description)
     * withSchema()
     * withDefinition()
     * withSecurity()
     */

    /**
     * @return Manifest
     * @deprecated
     */
    public function endSwagger(): Manifest
    {
        return $this->builder;
    }

    public function endOpenAPI(): Manifest
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
}
