<?php

namespace go1\rest\examples;

use go1\rest\wrapper\request\rabbitmq\Client;
use go1\rest\wrapper\request\rabbitmq\Message;

class RabbitMqExample
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function publishing()
    {
        $this
            ->client
            ->channel($exchange = 'events', $type = 'topic')
            ->publish(
                Message::create(
                    $routingKey = 'm.1',
                    $payload = 'hi there',
                    $headers = ['time' => time()]
                )
            );
    }
}
