<?php

namespace go1\rest\util;

use function property_exists;

abstract class Model
{
    private $___onInit = false;

    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }

    public function setInit(bool $init)
    {
        if ($init) {
            $this->___onInit = true;
        } else {
            unset($this->___onInit);
        }
    }

    public function __set(string $name, $value)
    {
        if ($this->___onInit) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }
}
