<?php

namespace go1\rest\wrapper\request\rabbitmq;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Message extends AMQPMessage
{
    private $routingKey;

    public static function create(string $routingKey, string $payload, array $headers = [])
    {
        $msg = new static($payload, [
            'content_type'        => 'application/json',
            'application_headers' => new AMQPTable($headers),
        ]);

        $msg->routingKey = $routingKey;

        return $msg;
    }

    public function routingKey(): string
    {
        return $this->routingKey;
    }
}
