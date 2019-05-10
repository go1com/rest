<?php

namespace go1\rest\wrapper\json_schema\property;

abstract class PropertySchemaBuilder
{
    const TYPE = 'abstract';

    protected $builder;
    protected $schema;

    /**
     * PropertySchemaBuilder constructor.
     *
     * @param ObjectPropertyBuilder|ArrayPropertyBuilder $builder
     * @param array                                      $schema
     */
    public function __construct($builder, array &$schema)
    {
        $this->builder = $builder;
        $this->schema = &$schema;

        $this->set('type', static::TYPE);
    }

    protected function set($k, $v)
    {
        $this->schema[$k] = $v;

        return $this;
    }

    /**
     * @param string $description
     * @return static
     */
    public function withDescription(string $description)
    {
        return $this->set('description', $description);
    }

    public function end()
    {
        return $this->builder;
    }
}
