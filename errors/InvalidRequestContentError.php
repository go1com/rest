<?php

namespace go1\rest\errors;

class InvalidRequestContentError extends RestError
{
    protected $httpErrorCode = 400;
}
