<?php

namespace go1\rest\wrapper\request;

class RequestBag
{
    private $items;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function all()
    {
        return $this->items;
    }

    public function get($key, $default = null)
    {
        return isset($this->items[$key]) ? $this->items[$key] : $default;
    }

    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }
}
