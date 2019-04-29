<?php

namespace go1\rest\errors;

class RuntimeError extends RestError
{
    protected $httpErrorCode = 500;
}
