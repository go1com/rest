<?php

namespace go1\rest\util;

use ReflectionObject;
use stdClass;
use function get_class;
use function is_null;
use function is_scalar;
use function preg_match;
use function str_replace;
use function strpos;
use function trim;

class Marshaller
{
    public function dump($obj, array $propertyFormat = ['json'])
    {
        $rObject = new ReflectionObject($obj);
        foreach ($rObject->getProperties() as $rProperty) {
            $_ = $this->property($rObject->getDocComment(), $rProperty->getDocComment(), $rProperty->getName(), $propertyFormat);
            list($path, $type, $options) = $_;
            if (!$path) {
                continue;
            }

            $raw = $obj->{$rProperty->getName()};
            if (is_scalar($raw) || empty($type)) {
                $value = $this->scalarCast($type, $raw);
            } elseif (false !== strpos($type, '[]')) { # Support UserClass[]
                $value = [];
                foreach ($obj->{$rProperty->getName()} as $v) {
                    $value[] = $this->dump($v, $propertyFormat);
                }
            } else {
                $value = $obj->{$rProperty->getName()};
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

    /**
     * @param stdClass $input
     * @param mixed    $obj
     * @param array    $propertyFormat
     * @return mixed Entity
     */
    public function parse(stdClass $input, $obj, array $propertyFormat = ['json'])
    {
        if ('stdClass' == get_class($obj)) {
            return $input;
        }

        $rObject = new ReflectionObject($obj);
        foreach ($rObject->getProperties() as $rProperty) {
            $_ = $this->property($rObject->getDocComment(), $rProperty->getDocComment(), $rProperty->getName(), $propertyFormat);
            list($path, $type) = $_;

            if (!$path || !isset($input->{$path})) {
                continue;
            }

            $value = null;

            if (is_scalar($input->{$path})) {
                $value = $this->scalarCast($type, $input->{$path});
            } elseif (false !== strpos($type, '[]')) {
                # Support UserClass[]
                $type = str_replace('[]', '', $type);
                $value = [];
                foreach ($input->{$path} as $v) {
                    $value[] = is_scalar($v)
                        ? $this->scalarCast($type, $v)
                        : $this->parse($v, new $type, $propertyFormat);
                }
            } else {
                $value = $input->{$path};
                $value = $this->parse($value, new $type, $propertyFormat);
            }

            $rProperty->setAccessible(true);
            $rProperty->setValue($obj, $value);
        }

        return $obj;
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
                preg_match('/@property\s+([^\s]+)\s*\\$' . $propertyName . '.*$/m', $classComment, $matches);
                if (!empty($matches[1])) {
                    $type = trim($matches[1]);
                }
            }
        }

        return [$path, $type, $options];
    }
}
