<?php

namespace go1\rest\wrapper;

use go1\rest\wrapper\service\ComposerBuilder;
use go1\rest\wrapper\service\DockerComposeBuilder;
use go1\rest\wrapper\service\PHPUnitConfigBuilder;
use go1\rest\wrapper\service\RestConfigBuilder;
use go1\rest\wrapper\service\StreamBuilder;
use go1\rest\wrapper\service\swagger\SwaggerBuilder;

class Manifest
{
    private $rest;
    private $stream;
    private $dockerCompose;
    private $composer;
    private $phpunit;
    private $swagger;

    public function __construct()
    {
        $this->rest = new RestConfigBuilder($this);
        $this->stream = new StreamBuilder($this);
        $this->dockerCompose = new DockerComposeBuilder($this);
        $this->composer = new ComposerBuilder($this);
        $this->phpunit = new PHPUnitConfigBuilder($this);
        $this->swagger = new SwaggerBuilder($this);
    }

    public static function create(): Manifest
    {
        return new Manifest;
    }

    public function composer(): ComposerBuilder
    {
        return $this->composer;
    }

    public function rest()
    {
        return $this->rest;
    }

    public function stream()
    {
        return $this->stream;
    }

    public function dockerCompose()
    {
        return $this->dockerCompose;
    }

    public function phpunit()
    {
        return $this->phpunit;
    }

    public function swagger()
    {
        return $this->swagger;
    }

    public function build()
    {
        return [
            'rest'           => $this->rest->build(),
            'docker-compose' => $this->dockerCompose->build(),
            'composer'       => $this->composer->build(),
            'phpunit'        => $this->phpunit->build(),
            'swagger'        => $this->swagger->build(),
        ];
    }
}
