<?php

namespace go1\rest\wrapper\service\open_api;

class ResponseBuilder
{
    private $builder;
    private $config;

    public function __construct(PathBuilder $path, array &$config)
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
        $builder = new ResponseContentBuilder($this, $this->config['content'][$contentType]);

        return $builder;
    }

    public function endResponse()
    {
        return $this->builder;
    }
}
