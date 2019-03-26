<?php

namespace go1\rest\wrapper\service\swagger;

class SwaggerPathBuilder
{
    private $swagger;
    private $config;

    public function __construct(SwaggerBuilder $swagger, &$config)
    {
        $this->swagger = $swagger;
        $this->config = &$config;
        $this->config['parameters'] = [];
    }

    public function withSummary(string $value)
    {
        $this->config['summary'] = $value;

        return $this;
    }

    public function withParam(string $name)
    {
        $_ = count($this->config['parameters']) - 1;
        $this->config['parameters'][$_]['name'] = $name;

        return new SwaggerParamBuilder($this, $this->config['parameters'][$_]);
    }

    public function withController($controller)
    {
        $this->config['#controller'] = $controller;

        return $this;
    }

    public function withMiddleware($middleware)
    {
        $this->config['#middleware'][] = $middleware;

        return $this;
    }

    public function responses(string $code)
    {
        $this->config['responses'][$code] = [];
        $builder = new SwaggerResponseBuilder($this, $this->config['responses'][$code]);

        return $builder;
    }

    public function end()
    {
        return $this->swagger;
    }
}
