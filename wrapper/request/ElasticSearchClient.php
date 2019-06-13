<?php

namespace go1\rest\wrapper\request;

use go1\rest\wrapper\ElasticSearchClients;

abstract class ElasticSearchClient
{
    const INDEX_NAME = 'abstract';

    private $wrapper;

    public function __construct(ElasticSearchClients $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    public function get()
    {
        return $this->wrapper->get(static::INDEX_NAME);
    }
}
