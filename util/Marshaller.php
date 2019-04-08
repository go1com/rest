<?php

namespace go1\rest\util;

use ReflectionObject;
use stdClass;

class Marshaller
{
    public function dump($obj, array $propertyFormat = ['json'])
    {
        $rObject = new ReflectionObject($obj);
        foreach ($rObject->getProperties() as $rProperty) {
            $_ = $this->property($rObject->getDocComment(), $rProperty->getDocComment(), $rProperty->getName(), $propertyFormat);
            list($path, $type) = $_;
            if (!$path) {
                continue;
            }

            switch ($type) {
                case 'int':
                    $value = (int) $obj->{$rProperty->getName()};
                    break;

                case 'float':
                    $value = (float) $obj->{$rProperty->getName()};
                    break;

                case 'string':
                    $value = (string) $obj->{$rProperty->getName()};
                    break;

                case 'bool':
                    $value = (bool) $obj->{$rProperty->getName()};
                    break;

                case '':
                    $value = $obj->{$rProperty->getName()};
                    break;

                default:
                    $value = $obj->{$rProperty->getName()};
                    if (!is_null($value)) {
                        $value = $this->dump($value, $propertyFormat);
                    }

                    break;
            }

            $result[$path] = $value;
        }

        return $result ?? [];
    }

    public function parse(stdClass $input, $obj, array $propertyFormat = ['json'])
    {
        $rObject = new ReflectionObject($obj);

        foreach ($rObject->getProperties() as $rProperty) {
            $_ = $this->property($rObject->getDocComment(), $rProperty->getDocComment(), $rProperty->getName(), $propertyFormat);
            list($path, $type) = $_;

            if (!$path || !isset($input->{$path})) {
                continue;
            }

            $value = null;

            switch ($type) {
                case 'int':
                    $value = (int) $input->{$path};
                    break;

                case 'float':
                    $value = (float) $input->{$path};
                    break;

                case 'string':
                    $value = (string) $input->{$path};
                    break;

                case 'bool':
                    $value = (bool) $input->{$path};
                    break;

                default:
                    $value = $input->{$path};
                    $value = $this->parse($value, new $type);
                    break;
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
        }

        if ($classComment) {
            preg_match('/@property\s+([^\s]+)\s*\\$' . $propertyName . '.*$/m', $classComment, $matches);
            if (!empty($matches[1])) {
                $type = trim($matches[1]);
            }
        }

        return [$path, $type];
    }
}
