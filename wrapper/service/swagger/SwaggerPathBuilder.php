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

    public function end()
    {
        return $this->swagger;
    }
}
