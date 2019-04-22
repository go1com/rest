<?php

namespace go1\rest\tests;

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use go1\rest\RestService;
use go1\rest\Stream;
use go1\rest\util\MessageFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function define;
use function defined;
use function dirname;

abstract class RestTestCase extends TestCase implements ContainerInterface
{
    /**
     * @var MessageFactory
     */
    protected $mf;
    protected $committed;
    protected $rest;

    /**
     * A shared connection for all services.
     *
     * @var Connection
     */
    protected $db;

    /**
     * Enable to auto process POST /install on every test cases.
     *
     * @var bool
     */
    protected $hasInstallRoute = false;

    protected function setUp(): void
    {
        $this->rest(/* Just make sure all install logic are executed */);
    }

    public function mf(): MessageFactory
    {
        if (null === $this->mf) {
            $this->mf = new MessageFactory;
        }

        return $this->mf;
    }

    protected function db(): Connection
    {
        if ($this->db) {
            return $this->db;
        }

        return $this->db = DriverManager::getConnection(['url' => 'sqlite://sqlite::memory:']);
    }

    public function tearDown(): void
    {
        $this->rest = null;
        $this->db = null;
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

    protected function stream(): Stream
    {
        return $this->rest()->getContainer()->get(Stream::class);
    }

    protected function install(RestService $rest)
    {
        /** @var Container $c */
        $c = $rest->getContainer();

        $this->stream()->addTransport(
            function (string $event, string $payload, array $context) {
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

            $this->assertContains($res->getStatusCode(), [200, 204, 404, 405, 403]);
        }

        // [REST.INSTALL] Stream base
        // ---------------------
        $this->stream()->commit('rest.install', '');
    }

    public function get($id)
    {
        return $this->rest()->getContainer()->get($id);
    }

    public function has($id)
    {
        return $this->rest()->getContainer()->has($id);
    }
}
