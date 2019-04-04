<?php

namespace go1\rest\tests;

use DI\Container;
use go1\rest\RestService;
use go1\rest\wrapper\MessageFactory;
use PHPUnit\Framework\TestCase;

abstract class RestTestCase extends TestCase
{
    /**
     * @var MessageFactory
     */
    protected $mf;
    protected $committed;
    protected $rest;

    /**
     * Enable to auto process POST /install on every test cases.
     *
     * @var bool
     */
    protected $hasInstallRoute = false;

    public function mf(): MessageFactory
    {
        if (null === $this->mf) {
            $this->mf = new MessageFactory;
        }

        return $this->mf;
    }

    public function tearDown(): void
    {
        $this->rest = null;
    }

    protected function rest(): RestService
    {
        if (!$this->rest) {
            if (!defined('REST_ROOT')) {
                define('REST_ROOT', dirname(__DIR__));
                define('REST_MANIFEST', __DIR__ . '/../examples/manifest.php');
            }

            /** @var RestService $rest */
            $this->rest = require __DIR__ . '/../public/index.php';
            $this->install($this->rest);
        }

        return $this->rest;
    }

    protected function install(RestService $rest)
    {
        /** @var Container $c */
        $c = $rest->getContainer();
        $stream = $rest->stream();

        $stream->addTransport(
            function (string $event, string $payload, array $context) use (&$stream) {
                $this->committed[$event][] = [$payload, $context];
            }
        );

        // ---------------------
        // Mock database connections
        // ---------------------
        if ($c->has('dbOptions')) {
            foreach ($c->get('dbOptions') as $name => $options) {
                $override[$name]['url'] = 'sqlite://sqlite::memory:';
            }

            $c->set('dbOptions', $override ?? []);
        }

        // [REST.INSTALL] RESTFUL base — POST /install
        // ---------------------
        if ($this->hasInstallRoute) {
            $res = $rest->process(
                $this->mf()->createRequest('POST', '/install'),
                $this->mf()->createResponse()
            );

            $this->assertContains($res->getStatusCode(), [200, 204, 404]);
        }

        // [REST.INSTALL] Stream base
        // ---------------------
        $stream->commit('rest.install', '');
    }
}
