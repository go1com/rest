<?php

namespace go1\rest\wrapper;

use DI\Container;
use go1\rest\tests\RestTestCase;
use RuntimeException;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\MemcachedCache;
use Memcached;

class CacheClients
{
    private $container;

    public function __construct(Container &$c)
    {
        $this->container = $c;
    }

    public function get(string $name, bool $arrayCacheForTesting = true): Psr16CacheInterface
    {
        if ($arrayCacheForTesting && class_exists(RestTestCase::class, false)) {
            return new ArrayCache;
        }

        if (!$this->container->has("cache.$name")) {
            $o = $this->container->get('cacheOptions');
            switch ($name) {
                case 'memcached':
                    if (!class_exists(Memcached::class)) {
                        throw new RuntimeException('Missing caching driver.');
                    }

                    $host = $o[$name]['host'];
                    $port = $o[$name]['port'];

                    $memcached = new Memcached($name);
                    $memcached->addServer($host, $port);
                    return new MemcachedCache($memcached);
                default:
                    throw new RuntimeException('Unsupported backend: ' . $name);
            }
        }

        return $this->container->get("cache.$name");
    }
}
