<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\service\entity\EntityPropertyBuilder;

class EntityTypeBuilder
{
    private $api;
    private $config;

    public function __construct(EntityApiBuilder $api, array &$config)
    {
        $this->api = $api;
        $this->config = &$config;
    }

    public function withLabel(string $label)
    {
        $this->config['label'] = $label;

        return $this;
    }

    public function withBaseTable(string $name)
    {
        $this->config['table_base'] = $name;

        return $this;
    }

    public function withProperty(string $name)
    {
        $this->config['property'][$name] = [];

        return new EntityPropertyBuilder($this, $this->config['property']);
    }

    public function end()
    {
        return $this->api;
    }
}
