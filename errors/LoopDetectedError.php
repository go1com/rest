<?php

namespace go1\rest\errors;

class LoopDetectedError extends RestError
{
    protected $httpErrorCode = 508;
}
