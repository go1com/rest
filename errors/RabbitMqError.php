<?php

namespace go1\rest\errors;

class RabbitMqError extends RestError
{
    protected $httpErrorCode = 500;
}
