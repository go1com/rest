<?php

namespace go1\rest\wrapper\service\swagger;

use go1\rest\wrapper\Manifest;

class SwaggerBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = ['paths' => []];
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

    public function withPath(string $path, string $method, $controller = null): SwaggerPathBuilder
    {
        $this->config['paths'][$path][$method] = [];
        $pathBuilder = new SwaggerPathBuilder($this, $this->config['paths'][$path][$method]);

        if ($controller) {
            $pathBuilder->withController($controller);
        }

        return $pathBuilder;
    }

    public function getPaths()
    {
        return $this->config['paths'];
    }

    /**
     * withTag($name, $description)
     *
     * withExternalDocs($url, $description)
     * withSchema()
     * withDefinition()
     * withSecurity()
     */

    public function endSwagger(): Manifest
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
