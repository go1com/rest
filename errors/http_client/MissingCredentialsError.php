<?php

namespace go1\rest\errors\http_client;

use go1\rest\errors\RestError;

class MissingCredentialsError extends RestError
{
    protected $httpErrorCode = 500;
}
