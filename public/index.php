<?php

use go1\rest\RestService;

return call_user_func(function () {
    if (!defined('APP_ROOT')) {
        define('APP_ROOT', dirname(__DIR__));
    }

    require_once APP_ROOT . '/vendor/autoload.php';
    $cnf = is_file($_file = APP_ROOT . '/resources/config.php') ? $_file : APP_ROOT . '/resources/config.default.php';
    $cnf = require $cnf;
    $app = new RestService($cnf);

    return ('cli' === php_sapi_name()) ? $app : $app->run();
});
