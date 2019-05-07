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

    private function parseRefSchema(array &$conf)
    {
        foreach ($conf as $k => &$v) {
            if (is_array($v)) {
                $this->parseRefSchema($v);
            }
        }

        if (isset($conf['schema']['$ref']) && is_scalar($conf['schema']['$ref']) && file_exists($conf['schema']['$ref'])) {
            $conf['schema'] = json_decode(file_get_contents($conf['schema']['$ref']), true);
        }
    }

    public function openAPIformat(): self
    {
        foreach ($this->config['paths'] as $path => &$pathConfig) {
            foreach ($pathConfig as $method => $conf) {
                unset($conf['#controller']);
                unset($conf['#middleware']);
                $conf['parameters'] = array_values($conf['parameters']);

                $this->parseRefSchema($conf);
                $pathConfig[strtolower($method)] = $conf;
                unset($pathConfig[$method]);
            }
        }

        return $this;
    }
}
