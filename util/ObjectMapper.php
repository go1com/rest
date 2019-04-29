<?php

namespace go1\rest\util;

use ReflectionObject;
use stdClass;

class ObjectMapper
{
    /**
     * @param stdClass $input
     * @param object   $obj
     * @param bool     $skipNonExistProperty
     * @return mixed Entry.
     * @throws \ReflectionException
     */
    public function map(stdClass $input, $obj, bool $skipNonExistProperty = true)
    {
        $rObj = new ReflectionObject($obj);
        foreach ($input as $k => $v) {
            if ($skipNonExistProperty && !$rObj->hasProperty($k)) {
                continue;
            }

            $rProperty = $rObj->getProperty($k);
            $rProperty->setAccessible(true);
            if (is_scalar($v)) {
                $rProperty->setValue($obj, $v);
            } else {
                $className = $this->propertyType($rProperty->getDocComment());
                if ($className) {
                    $rProperty->setValue($obj, $this->map($v, new $className));
                } else {
                    $rProperty->setValue($obj, $v);
                }
            }
        }

        return $obj;
    }

    private function propertyType($comment)
    {
        preg_match_all('/@var(?:[ \t]+(?P<type>.*?))?[ \t]*\r?$/m', $comment, $matches);

        return $matches['type'][0] ?? null;
    }
}
