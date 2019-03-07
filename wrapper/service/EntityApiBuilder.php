<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\Manifest;

class EntityApiBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = [];
    }

    public function entityType(string $type)
    {
        $this->config[$type] = [];

        return new EntityTypeBuilder($this, $this->config[$type]);
    }
}
