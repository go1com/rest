<?php

namespace go1\rest\commands;

use go1\rest\wrapper\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerBuilderCommand extends Command
{
    public function __construct()
    {
        parent::__construct('compose');

        $this->addArgument('path', InputArgument::REQUIRED, 'Path to manifest file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Manifest $manifest */
        $manifest = require $input->getArgument('path');
        $_ = $manifest->composer()->build();
        $_ = json_encode($_, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        echo $_;
    }
}
