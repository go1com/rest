<?php

namespace go1\rest\errors;

class InternalResourceError extends RestError
{
    protected $httpErrorCode = 403;
}
