<?php

namespace go1\rest\wrapper\json_schema\property;

class ArrayPropertyBuilder extends PropertySchemaBuilder
{
    const TYPE = 'array';

    public function withIntegerItems()
    {
        $this->schema['items'] = [];

        return new IntegerPropertyBuilder($this, $this->schema['items']);
    }

    public function withNumberItems()
    {
        $this->schema['items'] = [];

        return new NumberPropertyBuilder($this, $this->schema['items']);
    }

    public function withStringItems()
    {
        $this->schema['items'] = [];

        return new StringPropertyBuilder($this, $this->schema['items']);
    }
}
