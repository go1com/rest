<?php

namespace go1\rest\wrapper\service\open_api;

class ResponseContentBuilder
{
    private $builder;
    private $config;

    public function __construct(ResponseBuilder $builder, array &$config)
    {
        $this->builder = $builder;
        $this->config = &$config;
    }

    public function withSchema(array $jsonSchema)
    {
        $this->config['schema'] = $jsonSchema;

        return $this;
    }

    public function endResponseContent()
    {
        return $this->builder;
    }
}
