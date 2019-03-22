<?php

use go1\rest\wrapper\Manifest;

return call_user_func(
    function () {
        if (!defined('REST_ROOT')) {
            define('REST_ROOT', dirname(__DIR__));
        }

        require_once REST_ROOT . '/vendor/autoload.php';

        /** @var Manifest $manifest */
        $manifest = defined('REST_MANIFEST') ? REST_MANIFEST : (__DIR__ . '/../manifest.php');
        $manifest = require $manifest;
        $service = $manifest->rest()->get();

        return ('cli' === php_sapi_name()) ? $service : $service->run();
    }
);
