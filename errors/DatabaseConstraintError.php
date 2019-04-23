<?php

namespace go1\rest\errors;

class DatabaseConstraintError extends RestError
{
    protected $httpErrorCode = 400;
}
