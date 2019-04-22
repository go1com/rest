<?php

namespace go1\rest\wrapper\service\swagger;

use function realpath;
use function strpos;

/**
 * ref https://swagger.io/docs/specification/describing-parameters/
 */
class SwaggerParamBuilder
{
    private $pathBuilder;
    private $config;

    public function __construct(SwaggerPathBuilder $pathBuilder, array &$config)
    {
        $this->pathBuilder = $pathBuilder;
        $this->config = &$config;
    }

    public function inPath()
    {
        $this->config['in'] = 'path';

        return $this;
    }

    public function inBody()
    {
        $this->config['in'] = 'body';

        return $this;
    }

    public function inHeader()
    {
        $this->config['in'] = 'header';

        return $this;
    }

    public function inQuery()
    {
        $this->config['in'] = 'query';

        return $this;
    }

    public function required(bool $bool)
    {
        $this->config['required'] = $bool;

        return $this;
    }

    public function withDefaultValue($value)
    {
        $this->config['schema']['default'] = $value;

        return $this;
    }

    public function withTypeString()
    {
        $this->config['schema']['type'] = 'string';

        return $this;
    }

    public function withTypeObject(string $ref)
    {
        if (false === strpos($ref, 'file://')) {
            $ref = 'file://' . realpath($ref);
        }

        $this->config['schema']['$ref'] = $ref;

        return $this;
    }

    public function withTypeInteger(string $format = 'int64')
    {
        $this->config['schema']['type'] = 'integer';
        $this->config['schema']['format'] = $format;

        return $this;
    }

    public function end()
    {
        return $this->pathBuilder;
    }
}
