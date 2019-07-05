<?php

namespace go1\rest\wrapper\request\rabbitmq;

use DI\Container;
use go1\rest\errors\InvalidServiceConfigurationError;
use go1\rest\errors\RabbitMqError;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Throwable;
use function parse_url;

class Client
{
    private $connectionUrl;
    private $connection;
    private $channels = [];

    public function __construct(Container $c)
    {
        $_ = $c->has('client.rabbitmq.url') ? $c->get('client.rabbitmq.url') : null;
        $_ = $_ ?: (getenv('CLIENT_RABBITMQ_URL') ?: getenv('QUEUE_URL'));

        if (!$_) {
            throw new InvalidServiceConfigurationError('missing rabbitMQ connection URL.');
        }

        $this->connectionUrl = $_;
    }

    public function connection(): AMQPStreamConnection
    {
        if (!$this->connection) {
            $_ = parse_url($this->connectionUrl);

            try {
                $this->connection = new AMQPStreamConnection($_['host'] ?? 'rabbitmq', $_['port'] ?? '5672', $_['user'] ?? '', $_['pass'] ?? '');
            } catch (Throwable $e) {
                RabbitMqError::throw('failed making connection: ' . $e->getMessage());
            }
        }

        return $this->connection;
    }

    public function channel(string $exchange, string $type): Channel
    {
        if (empty($this->channels[$exchange][$type])) {
            $this->channels[$exchange][$type] = Channel::create($this->connection(), $exchange, $type);
        }

        return $this->channels[$exchange][$type];
    }
}
