<?php

namespace go1\rest\errors;

class ResourceNotFoundError extends RestError
{
    protected $httpErrorCode = 404;
}
