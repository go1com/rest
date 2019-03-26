<?php

namespace go1\rest\wrapper\service\swagger;

class SwaggerResponseContentBuilder
{
    private $builder;
    private $config;

    public function __construct(SwaggerResponseBuilder $builder, array &$config)
    {
        $this->builder = $builder;
        $this->config = &$config;
    }

    public function withSchema(array $jsonSchema)
    {
        $this->config['schema'] = $jsonSchema;

        return $this;
    }

    public function end()
    {
        return $this->builder;
    }
}
