<?php

namespace go1\rest\tests;

use go1\rest\RestService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class RestTestCase extends TestCase
{
    protected function app(): RestService
    {
        if (!defined('APP_ROOT')) {
            throw new RuntimeException('APP_ROOT is not defined');
        }

        /** @var RestService $app */
        $app = require __DIR__ . '/../public/index.php';
        $this->install($app);

        return $app;
    }

    protected function install(RestService $service)
    {
    }
}
