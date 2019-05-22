<?php

namespace go1\rest\wrapper;

use DI\Container;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use RuntimeException;

class ElasticSearchClients
{
    private $options;
    private $builder;

    public function __construct(Container $container, ClientBuilder $builder)
    {
        $this->options = $container->get('esOptions');

        if (is_string($this->options)) {
            $this->options = ['default' => ['endpoint' => $this->options]];
        }

        $this->builder = $builder;
    }

    public function default(): Client
    {
        return $this->get('default');
    }

    public function get(string $name): Client
    {
        if (!$host = $this->host($name)) {
            throw new RuntimeException('ElasticSearch endpoint not found: ' . $name);
        }

        return $this->builder->setHosts([$host])->build();
    }

    public function host(string $name)
    {
        if (isset($this->options[$name]['endpoint'])) {
            return parse_url($this->options[$name]['endpoint']);
        }

        if ('default' === $name) {
            if (isset($this->options[$name]['endpoint'])) {
                return parse_url($this->options[$name]['endpoint']);
            }
        }

        return null;
    }
}
