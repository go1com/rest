<?php

namespace go1\rest\util;

use DI\Container;
use ReflectionObject;
use stdClass;
use function apcu_fetch;
use function apcu_store;
use function function_exists;
use function get_class;
use function implode;
use function ini_get;
use function is_null;
use function is_scalar;
use function preg_match;
use function str_replace;
use function strpos;
use function trim;
use const PHP_SAPI;

class Marshaller
{
    private $cache = false;

    public function __construct(Container $container)
    {
        if ($container->has('marshaller.cache')) {
            if ($container->get('marshaller.cache')) {
                $this->cache = function_exists('apcu_fetch')
                    && ini_get('apc.enabled')
                    && !('cli' === PHP_SAPI && !ini_get('apc.enable_cli'));
            }
        }
    }

    public function setCache(bool $cache)
    {
        $this->cache = $cache;
    }

    public function dump($obj, array $propertyFormat = ['json'])
    {
        if ('stdClass' == get_class($obj)) {
            return (array) $obj;
        }

        $info = $this->objectInfo($obj, $propertyFormat);
        foreach ($info['properties'] as $name => $property) {
            list($path, $type, $options) = $property;
            if (!$path) {
                continue;
            }

            $raw = $obj->{$name};
            if (is_scalar($raw) || empty($type)) {
                $value = $this->scalarCast($type, $raw);
            } elseif (false !== strpos($type, '[]')) { # Support UserClass[]
                $value = [];
                foreach ($obj->{$name} as $v) {
                    $value[] = $this->dump($v, $propertyFormat);
                }
            } else {
                $value = $obj->{$name};
                if (!is_null($value)) {
                    $value = $this->dump($value, $propertyFormat);
                }
            }

            if (isset($options['omitEmpty']) && is_null($value)) {
                continue;
            }

            $result[$path] = $value;
        }

        return $result ?? [];
    }

    /**
     * @param stdClass $input
     * @param Model    $obj
     * @param array    $propertyFormat
     * @return mixed Entity
     */
    public function parse(stdClass $input, $obj, array $propertyFormat = ['json'])
    {
        if ('stdClass' == get_class($obj)) {
            return $input;
        }

        $obj->setInit(true);
        $info = $this->objectInfo($obj, $propertyFormat);
        foreach ($info['properties'] as $name => $property) {
            list($path, $type) = $info['properties'][$name];

            if (!$path || !isset($input->{$path})) {
                continue;
            }

            $value = null;
            if (is_scalar($input->{$path})) {
                $value = $this->scalarCast($type, $input->{$path});
            } elseif (false !== strpos($type, '[]')) {
                $type = str_replace('[]', '', $type); # Support UserClass[]
                $value = [];
                foreach ($input->{$path} as $v) {
                    $value[] = is_scalar($v)
                        ? $this->scalarCast($type, $v)
                        : $this->parse($v, new $type);
                }
            } else {
                $value = $input->{$path};
                $value = $this->parse($value, new $type, $propertyFormat);
            }

            $obj->{$name} = $value;
        }

        $obj->setInit(false);

        return $obj;
    }

    private function scalarCast($type, $value)
    {
        switch ($type) {
            case 'int':
                return (int) $value;

            case 'float':
                return (float) $value;

            case 'string':
                return (string) $value;

            case 'bool':
                return (bool) $value;

            default:
                return $value;
        }
    }

    private function objectInfo(&$obj, array $propertyFormat = ['json'])
    {
        $class = get_class($obj);
        $key = $class . ':' . implode(',', $propertyFormat);
        if ($this->cache) {
            if (false === strpos($class, '@anonymous')) {
                $cache = apcu_fetch($key);
                if ($cache) {
                    return $cache;
                }
            }
        }

        # class comment
        # properties: name, comment
        $rObject = new ReflectionObject($obj);
        $comment = $rObject->getDocComment();
        $info = [
            'comment'    => $comment,
            'properties' => [],
        ];

        foreach ($rObject->getProperties() as $rProperty) {
            $pName = $rProperty->getName();
            list($path, $type, $options) = $this->property($comment, $rProperty->getDocComment(), $pName, $propertyFormat);
            $type = explode(' ', $type)[0];

            $info['properties'][$pName] = [$path, $type, $options];
        }

        if ($this->cache) {
            apcu_store($key, $info);
        }

        return $info;
    }

    private function property(string $classComment, string $propertyComment, string $propertyName, array $propertyFormats)
    {
        $path = $propertyName;
        $type = null;
        $options = [];

        if ($propertyComment) {
            # parse `@var STRING` doc-block
            preg_match_all('/@var(?:[ \t]+(?P<type>.*?))?[ \t]*\r?$/m', $propertyComment, $matches);
            if (!empty($matches['type'][0])) {
                $type = trim($matches['type'][0]);
            }

            foreach ($propertyFormats as $propertyFormat) {
                # parse `@json STRING` doc-block
                preg_match('/@' . $propertyFormat . '\s+([^\s]+)\s*$/m', $propertyComment, $matches);
                if (!empty($matches[1])) {
                    $path = $matches[1];
                }
            }

            # parse `@omitEmpty` doc-block
            if (preg_match('/@omitEmpty/', $propertyComment, $_)) {
                $options['omitEmpty'] = true;
            }
        }

        if ($classComment) {
            if (!$type) {
                preg_match('/@property\s+([^\s]+)\s*\\$' . $propertyName . '\s.*$/m', $classComment, $matches);
                if (!empty($matches[1])) {
                    $type = trim($matches[1]);
                }
            }
        }

        return [$path, $type, $options];
    }
}
