<?php

namespace go1\rest\controller\health;

use DI\Container;
use Doctrine\DBAL\DriverManager;
use go1\rest\wrapper\ElasticSearchClients;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class HealthCollectorDefault
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function check(HealthCollectorEvent $event)
    {
        $this->container->getKnownEntryNames();

        if ($this->container->has('dbOptions')) {
            $this->pingDatabases($event);
        }

        if ($this->container->has('esOptions')) {
            $this->pingElasticSearchServers($event);
        }

        if ($this->container->has('cacheConnectionUrl')) {
            $this->pingCacheServer($event);
        }
    }

    private function pingDatabases(HealthCollectorEvent $event)
    {
        foreach ($this->container->get('dbOptions') as $name => $options) {
            $conn = DriverManager::getConnection($options);
            $event->set("db.{$name}", 'ping', !$conn->ping());
        }
    }

    private function pingElasticSearchServers(HealthCollectorEvent $event)
    {
        foreach ($this->container->get('esOptions') as $name => $url) {
            $event->set(
                "elasticsearch.{$name}",
                'ping',
                !$this->container
                    ->get(ElasticSearchClients::class)
                    ->get($name)
                    ->ping()
            );
        }
    }

    private function pingCacheServer(HealthCollectorEvent $event)
    {
        try {
            $cache = $this->container->get(CacheInterface::class);
            $cache->get('ping');
            $event->set("cache.default", 'ping', false);
        } catch (Throwable $e) {
            $event->set("cache.default", 'ping', true);
        }
    }
}
