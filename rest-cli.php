#!/usr/bin/env php
<?php

namespace go1\rest;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $loader = require __DIR__ . '/vendor/autoload.php';
} else {
    $loader = require __DIR__ . '/../../autoload.php';
}

use go1\rest\commands\ComposerBuilderCommand;
use go1\rest\commands\DockerComposeBuilderCommand;
use go1\rest\commands\SwaggerBuilderCommand;
use Symfony\Component\Console\Application;
use function call_user_func;
use function file_exists;
use function getenv;

$cli = new Application();
$cli->add(new DockerComposeBuilderCommand);
$cli->add(new ComposerBuilderCommand);
$cli->add(new SwaggerBuilderCommand());

$hook = getenv('REST_CLI_HOOK');
if ($hook && file_exists($hook)) {
    call_user_func(require $hook, $loader, $cli);
}

$cli->run();
