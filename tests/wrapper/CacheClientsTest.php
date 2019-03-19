<?php

namespace go1\rest\tests\wrapper;

use DI\Container;
use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\CacheClients;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\MemcachedCache;

class CacheClientsTest extends RestTestCase
{
    public function test()
    {
        $cache = $this->rest()->getContainer()->get(CacheClients::class)->get('array');
        $this->assertTrue($cache instanceof ArrayCache);
    }

    public function testMemcachedClient()
    {
        $rest = $this->rest();

        /** @var Container $c */
        $c = $rest->getContainer();
        $c->set('cacheOptions', [
            'memcached' => [
                'host' => 'http://localhost',
                'port' => 1122,
            ],
        ]);

        $cache = $c->get(CacheClients::class)->get('memcached', false);
        $this->assertTrue($cache instanceof MemcachedCache);
    }
}
