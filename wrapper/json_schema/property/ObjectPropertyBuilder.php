<?php

namespace go1\rest\wrapper\json_schema\property;

class ObjectPropertyBuilder extends PropertySchemaBuilder
{
    const TYPE = 'object';

    public function withRequired(array $properties)
    {
        return $this->set('required', $properties);
    }

    public function withIntegerProperty(string $name)
    {
        $this->schema['properties'][$name] = [];

        return new IntegerPropertyBuilder($this, $this->schema['properties'][$name]);
    }

    public function withNumberProperty(string $name)
    {
        $this->schema['properties'][$name] = [];

        return new NumberPropertyBuilder($this, $this->schema['properties'][$name]);
    }

    public function withStringProperty(string $name)
    {
        $this->schema['properties'][$name] = [];

        return new StringPropertyBuilder($this, $this->schema['properties'][$name]);
    }

    public function withArrayProperty(string $name)
    {
        $this->schema['properties'][$name] = [];

        return new ArrayPropertyBuilder($this, $this->schema['properties'][$name]);
    }
}
