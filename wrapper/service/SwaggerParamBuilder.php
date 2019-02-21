<?php

namespace go1\rest\wrapper\service;

class SwaggerParamBuilder
{
    private $pathBuilder;
    private $config;

    public function __construct(SwaggerPathBuilder $pathBuilder, array &$config)
    {
        $this->pathBuilder = $pathBuilder;
        $this->config = &$config;
    }

    public function inPath()
    {
        $this->config['in'] = 'path';

        return $this;
    }

    public function inQuery()
    {
        $this->config['in'] = 'query';

        return $this;
    }

    public function required(bool $bool)
    {
        $this->config['required'] = $bool;

        return $this;
    }

    public function withTypeString()
    {
        $this->config['schema']['type'] = 'string';

        return $this;
    }

    public function withTypeInteger(string $format = 'int64')
    {
        $this->config['schema']['type'] = 'integer';
        $this->config['schema']['format'] = $format;

        return $this;
    }

    public function end()
    {
        return $this->pathBuilder;
    }
}
