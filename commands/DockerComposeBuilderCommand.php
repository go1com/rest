<?php

namespace go1\rest\commands;

use go1\rest\wrapper\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DockerComposeBuilderCommand extends Command
{
    public function __construct()
    {
        parent::__construct('docker-compose');

        $this->addArgument('path', InputArgument::REQUIRED, 'Path to manifest file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Manifest $manifest */
        $manifest = require $input->getArgument('path');
        $_ = $manifest->dockerCompose()->build();
        $_ = Yaml::dump($_, Yaml::DUMP_OBJECT_AS_MAP);
        $_ = str_replace("'%IMAGE_URL%'", '${CI_REGISTRY_IMAGE}:${DOCKER_TAG}', $_);

        echo $_;
    }
}
