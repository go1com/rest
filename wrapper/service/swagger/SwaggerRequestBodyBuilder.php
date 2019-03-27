<?php

namespace go1\rest\wrapper\service\swagger;

class SwaggerRequestBodyBuilder
{
    private $builder;
    private $config;

    public function __construct(SwaggerPathBuilder $builder, array &$config)
    {
        $this->builder = $builder;
        $this->config = &$config;
    }

    public function withDescription(string $description)
    {
        $this->config['description'] = $description;

        return $this;
    }

    public function withRequired(bool $required)
    {
        $this->config['required'] = $required;

        return $this;
    }

    public function withContent(string $contentType)
    {
        $this->config['content'][$contentType] = [];
        $builder = new SwaggerRequestContentBuilder($this, $this->config['content'][$contentType]);

        return $builder;
    }

    public function endRequestBody()
    {
        return $this->builder;
    }
}
