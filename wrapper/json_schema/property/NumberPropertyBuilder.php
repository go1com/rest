<?php

namespace go1\rest\wrapper\json_schema\property;

class NumberPropertyBuilder extends PropertySchemaBuilder
{
    const TYPE = 'number';

    public function withMaximum($maximum)
    {
        return $this->set('maximum', $maximum);
    }

    public function withMinimum($minimum)
    {
        return $this->set('minimum', $minimum);
    }
}
