<?php

namespace go1\rest\tests\wrapper;

use DI\Container;
use go1\rest\tests\RestTestCase;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\MemcachedCache;
use Symfony\Component\Cache\Simple\RedisCache;

class CacheClientTest extends RestTestCase
{
    public function test()
    {
        $rest = $this->rest();

        /** @var Container $c */
        $c = $rest->getContainer();
        $c->set('cacheConnectionUrl', 'array://localhost');

        $cache = $c->get(Psr16CacheInterface::class);
        $this->assertTrue($cache instanceof ArrayCache);
    }

    public function testMemcachedClient()
    {
        if (!class_exists(\Memcached::class)) {
            $this->markTestSkipped('Missing memcached');
        }

        $rest = $this->rest();

        /** @var Container $c */
        $c = $rest->getContainer();
        $c->set('cacheConnectionUrl', 'memcached://localhost:1122');

        $cache = $c->get(Psr16CacheInterface::class);
        $this->assertTrue($cache instanceof MemcachedCache);
    }

    public function testRedisClient()
    {
        if (!class_exists(\Redis::class)) {
            $this->markTestSkipped('Missing redis');
        }

        $rest = $this->rest();

        /** @var Container $c */
        $c = $rest->getContainer();
        $c->set('cacheConnectionUrl', 'redis://localhost:6379');

        $cache = $c->get(Psr16CacheInterface::class);
        $this->assertTrue($cache instanceof RedisCache);
        $cache->set('foo', 'bar');
        $this->assertEquals('bar', $cache->get('foo'));
    }
}
