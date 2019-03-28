<?php

namespace go1\rest\errors;

use DomainException;

abstract class RestError extends DomainException
{
    protected function errorCode()
    {
        switch (static::class) {
            case InternalResourceError::class:
                return 1;

            default:
                return 0;
        }
    }
}
