<?php

namespace go1\rest\controller\health;

use DI\Container;
use Doctrine\DBAL\DriverManager;
use go1\rest\wrapper\ElasticSearchClients;
use go1\rest\wrapper\request\Http;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class HealthCollectorDefault
{
    private $container;
    private $http;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->http = $this->container->get(Http::class);
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

        if ($this->container->has('services')) {
            $this->pingServices($event);
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

    private function pingServices(HealthCollectorEvent $event)
    {
        $error = function (string $service): bool {
            $uri = $this->http->serviceUri($service, '/');
            $req = $this->http->createRequest('GET', $uri);

            try {
                $res = $this->http->sendRequest($req);

                return $res->getStatusCode() >= 300;
            } catch (\Throwable $e) {
                return true;
            }
        };

        foreach ($this->container->get('services') as $service) {
            $event->set("service.{$service}", 'ping', $error($service));
        }
    }
}
