<?php

namespace go1\rest\errors;

class InvalidServiceConfigurationError extends RestError
{
    protected $httpErrorCode = 506;
}
