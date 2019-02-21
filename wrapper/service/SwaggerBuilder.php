<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\ConfigBuilder;

class SwaggerBuilder
{
    private $builder;
    private $config = [];

    public function __construct(ConfigBuilder $builder)
    {
        $this->builder = $builder;
        $this->config = [];
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

    public function withPath(string $path, string $method): SwaggerPathBuilder
    {
        $this->config['paths'][$path][$method] = [];
        $pathBuilder = new SwaggerPathBuilder($this, $this->config['paths'][$path][$method]);

        return $pathBuilder;
    }

    /**
     * withTag($name, $description)
     *
     * withExternalDocs($url, $description)
     * withSchema()
     * withDefinition()
     * withSecurity()
     */

    public function end(): ConfigBuilder
    {
        return $this->builder;
    }
}
