#!/usr/bin/env php
<?php

namespace go1\rest;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    require __DIR__ . '/../../autoload.php';
}

use go1\rest\commands\ComposerBuilderCommand;
use go1\rest\commands\DockerComposeBuilderCommand;
use go1\rest\commands\SwaggerBuilderCommand;
use Symfony\Component\Console\Application;

$cli = new Application();
$cli->add(new DockerComposeBuilderCommand);
$cli->add(new ComposerBuilderCommand);
$cli->add(new SwaggerBuilderCommand);
$cli->run();
