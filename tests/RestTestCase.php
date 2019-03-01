<?php

namespace go1\rest\tests;

use go1\rest\RestService;
use Http\Message\MessageFactory\SlimMessageFactory;
use PHPUnit\Framework\TestCase;

abstract class RestTestCase extends TestCase
{
    protected $mf;
    protected $committed;

    public function mf(): SlimMessageFactory
    {
        if (null === $this->mf) {
            $this->mf = new SlimMessageFactory;
        }

        return $this->mf;
    }

    protected function rest(): RestService
    {
        if (!defined('APP_ROOT')) {
            define('REST_ROOT', dirname(__DIR__));
            define('REST_MANIFEST', __DIR__ . '/../examples/manifest.php');
        }

        /** @var RestService $rest */
        $rest = require __DIR__ . '/../public/index.php';
        $this->install($rest);

        return $rest;
    }

    protected function install(RestService $rest)
    {
        $rest->stream()->addTransport(
            function (string $event, string $payload, array $context) {
                $this->committed[$event][] = [$payload, $context];
            }
        );
    }
}
