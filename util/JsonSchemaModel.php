<?php

namespace go1\rest\util;

use function file_get_contents;
use function json_decode;

abstract class JsonSchemaModel extends Model
{
    const PATH = __FILE__; # path to schema.json; __FIFE_ is just for example

    public static function jsonSchema(bool $assoc = true)
    {
        return json_decode(file_get_contents(static::PATH), $assoc);
    }
}
