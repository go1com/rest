#!/usr/bin/env php
<?php

namespace go1\rest;

require __DIR__ . '/vendor/autoload.php';

use go1\rest\commands\ComposerBuilderCommand;
use go1\rest\commands\DockerComposeBuilderCommand;
use Symfony\Component\Console\Application;

$cli = new Application();
$cli->add(new DockerComposeBuilderCommand);
$cli->add(new ComposerBuilderCommand);
$cli->run();
