<?php

namespace go1\rest\wrapper\service\swagger;

class SwaggerResponseBuilder
{
    private $builder;
    private $config;

    public function __construct(SwaggerPathBuilder $path, array &$config)
    {
        $this->builder = $path;
        $this->config = &$config;
    }

    public function withDescription(string $description)
    {
        $this->config['description'] = $description;

        return $this;
    }

    public function withContent(string $contentType)
    {
        $this->config['content'][$contentType] = [];
        $builder = new SwaggerResponseContentBuilder($this, $this->config['content'][$contentType]);

        return $builder;
    }

    public function end()
    {
        return $this->builder;
    }
}
