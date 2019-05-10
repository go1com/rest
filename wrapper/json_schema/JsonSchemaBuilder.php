<?php

namespace go1\rest\wrapper\json_schema;

use go1\rest\wrapper\json_schema\property\ObjectPropertyBuilder;

class JsonSchemaBuilder extends ObjectPropertyBuilder
{
    public function __construct()
    {
        $this->set('type', 'object');
    }

    public function linkId(string $id)
    {
        return $this->set('$id', $id);
    }

    public function linkSchema(string $url)
    {
        return $this->set('$schema', $url);
    }

    public function withTitle(string $title)
    {
        return $this->set('title', $title);
    }

    public function buildArray()
    {
        return $this->schema;
    }

    public function buildJsonString(int $options = 0, int $depth = 512)
    {
        return json_encode($this->buildArray(), $options, $depth);
    }
}
