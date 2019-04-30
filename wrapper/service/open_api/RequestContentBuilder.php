<?php

namespace go1\rest\wrapper\service\open_api;

class RequestContentBuilder
{
    private $builder;
    private $config;

    public function __construct(RequestBodyBuilder $builder, array &$config)
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
