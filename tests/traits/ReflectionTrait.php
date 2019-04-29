<?php

namespace go1\rest\tests\traits;

use ReflectionObject;

trait ReflectionTrait
{
    public function getObjectProperty($obj, string $propertyName)
    {
        $rObject = new ReflectionObject($obj);
        $property = $rObject->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}
