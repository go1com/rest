<?php

namespace go1\rest\errors;

use DomainException;

abstract class RestError extends DomainException
{
    protected $httpErrorCode = 400;

    /**
     * @param string $msg
     * @return mixed
     */
    public static function throw(string $msg = '')
    {
        throw new static($msg);
    }

    public function errorCode()
    {
        switch (static::class) {
            case InternalResourceError::class:
                return 1;

            case InvalidRequestContentError::class:
                return 2;

            case InvalidServiceConfigurationError::class:
                return 3;

            case RuntimeError::class:
                return 4;

            case LoopDetectedError::class:
                return 5;

            case DatabaseConstraintError::class:
                return 6;

            case RabbitMqError::class:
                return 7;

            case MissingCredentialsError::class:
                return 8;

            case MissingScopeError::class:
                return 9;

            default:
                return 0;
        }
    }

    public function httpErrorCode()
    {
        return $this->httpErrorCode;
    }
}
