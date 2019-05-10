<?php

use go1\rest\RestService;
use go1\rest\wrapper\Manifest;

return call_user_func(
    function () {
        if (!defined('REST_ROOT')) {
            define('REST_ROOT', dirname(__DIR__));
        }

        if (!class_exists(RestService::class)) {
            require_once REST_ROOT . '/vendor/autoload.php';
        }

        /** @var Manifest $manifest */
        $path = getenv('REST_MANIFEST');
        $path = $path ?: (defined('REST_MANIFEST') ? REST_MANIFEST : (__DIR__ . '/../manifest.php'));
        $path = realpath($path);
        $manifest = require $path;
        $service = $manifest->rest()->get();
        $service->getContainer()->set('REST_MANIFEST', $path);

        return ('cli' === php_sapi_name()) ? $service : $service->run();
    }
);
