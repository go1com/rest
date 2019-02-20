<?php

namespace go1\rest\wrapper;

use DI\Container;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder as Builder;
use RuntimeException;

class ElasticSearchClients
{
    private $options;

    public function __construct(Container $container)
    {
        $this->options = $container->get('esOptions');
        if (is_string($this->options)) {
            $this->options = ['default' => ['endpoint' => $this->options],];
        }
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

        return Builder::create()->setHosts([$host])->build();
    }

    private function host(string $name): ?string
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
