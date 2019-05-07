<?php

namespace go1\rest\commands;

use go1\rest\wrapper\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SwaggerBuilderCommand extends Command
{
    public function __construct()
    {
        parent::__construct('swagger');

        $this->addArgument('path', InputArgument::REQUIRED, 'Path to manifest file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Manifest $manifest */
        $manifest = require $input->getArgument('path');
        $_ = $manifest->swagger()->openAPIformat()->build();

        echo json_encode($_);
    }
}
