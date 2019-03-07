<?php

namespace go1\rest\wrapper\service\entity;

use go1\rest\wrapper\service\EntityTypeBuilder;

class EntityPropertyBuilder
{
    private $builder;
    private $config;

    public function __construct(EntityTypeBuilder $builder, array &$config)
    {
        $this->builder = $builder;
        $this->config = &$config;
    }

    public function withLabel(string $label)
    {
        $this->config['label'] = $label;

        return $this;
    }

    public function withDescription(string $description)
    {
        $this->config['description'] = $description;

        return $this;
    }

    public function setReadOnly(bool $bool)
    {
        $this->config['read_only'] = $bool;

        return $this;
    }

    public function end()
    {
        return $this->builder;
    }
}
