<?php

namespace go1\rest\wrapper\service\swagger;

class SwaggerRequestContentBuilder
{
    private $builder;
    private $config;

    public function __construct(SwaggerRequestBodyBuilder $builder, array &$config)
    {
        $this->builder = $builder;
        $this->config = &$config;
    }

    public function withSchema(array $jsonSchema)
    {
        $this->config['schema'] = $jsonSchema;

        return $this;
    }

    public function endContent()
    {
        return $this->builder;
    }
}
