<?php

namespace go1\rest\commands;

use DI\Container;
use go1\rest\RestService;
use go1\rest\util\MessageFactory;
use go1\rest\wrapper\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function realpath;

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
        /** @var Container $container */
        $path = realpath($input->getArgument('path'));
        $manifest = require $path;
        $rest = $manifest->rest()->get();
        $container = $rest->getContainer();
        $container->set('REST_MANIFEST', $path);

        $mf = new MessageFactory;
        $req = $mf->createRequest('GET', '/api?jwt=' . RestService::SYSTEM_USER);

        try {
            $res = $rest->process($req, $mf->createResponse());
            if (200 == $res->getStatusCode()) {
                echo $res->bodyString();
            }
        } catch (\Throwable $e) {
        }
    }
}
