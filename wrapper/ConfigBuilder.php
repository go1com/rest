<?php

namespace go1\rest\wrapper;

use go1\rest\wrapper\service\ComposerBuilder;
use go1\rest\wrapper\service\DockerComposeBuilder;
use go1\rest\wrapper\service\ServiceConfigBuilder;

class ConfigBuilder
{
    private $serviceConfig;
    private $dockerCompose;
    private $composer;

    public function __construct()
    {
        $this->serviceConfig = new ServiceConfigBuilder($this);
        $this->dockerCompose = new DockerComposeBuilder($this);
        $this->composer = new ComposerBuilder($this);
    }

    public static function create(): ConfigBuilder
    {
        return new ConfigBuilder;
    }

    public function composer(): ComposerBuilder
    {
        return $this->composer;
    }

    public function serviceConfig()
    {
        return $this->serviceConfig;
    }

    public function dockerCompose()
    {
        return $this->dockerCompose;
    }

    public function build()
    {
        # @TODO Build resources/config.default.php
        # @TODO Build resources/ci/docker-compose.yml
    }
}
