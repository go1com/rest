<?php

namespace go1\rest\util;

use function property_exists;

abstract class Model
{
    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }
}
