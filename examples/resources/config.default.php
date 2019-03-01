<?php

use Monolog\Logger;

return [
    /* Example configuration file */
    'noDefaultEndpoint'            => true,
    'settings.displayErrorDetails' => true,
    Logger::class                  => function () { return new Logger('example'); },
];
