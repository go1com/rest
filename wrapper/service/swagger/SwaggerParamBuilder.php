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

    public function withTypeString(string $format = '')
    {
        $this->config['schema']['type'] = 'string';
        
        return $this->withFormat($format);
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

        return $this->withFormat($format);
    }

    public function withFormat(string $format)
    {
        if ($format) {
            $this->config['schema']['format'] = $format;
        }

        return $this;
    }

    /**
     * ref https://swagger.io/docs/specification/adding-examples/
     *
     * @param string $name
     * @param array  $example
     * @return static
     */
    public function withExample(string $name, array $example)
    {
        $this->config['examples'][$name] = $example;

        return $this;
    }

    public function end()
    {
        return $this->pathBuilder;
    }
}
