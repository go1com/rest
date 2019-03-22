<?php

namespace go1\rest\wrapper;

use DI\Container;
use go1\rest\tests\RestTestCase;
use Memcached;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use RuntimeException;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\MemcachedCache;

class CacheClients
{
    private $container;

    public function __construct(Container &$c)
    {
        $this->container = $c;
    }

    public function get(string $name, bool $arrayCacheForTesting = true): Psr16CacheInterface
    {
        static $caches;

        if (!empty($caches[$name])) {
            return $caches[$name];
        }

        if ($arrayCacheForTesting && class_exists(RestTestCase::class, false)) {
            return $caches[$name] = new ArrayCache;
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
                    return $caches[$name] = new MemcachedCache($memcached);
                default:
                    throw new RuntimeException('Unsupported backend: ' . $name);
            }
        }

        return $caches[$name] = $this->container->get("cache.$name");
    }
}
