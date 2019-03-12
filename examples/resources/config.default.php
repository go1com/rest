<?php

use Monolog\Logger;

return [
    /* Example configuration file */
    'noDefaultEndpoint'            => true,
    'settings.displayErrorDetails' => true,
    Logger::class                  => function () { return new Logger('example'); },
    'dbOptions' => [
        'default' => 'mysql://go1:go1@localhost:3306/go1_dev',
    ]
];
