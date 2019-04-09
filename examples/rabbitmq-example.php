<?php

namespace go1\rest\examples;

use go1\rest\wrapper\request\Message;
use go1\rest\wrapper\request\RabbitMqClient;

class RabbitMqExample
{
    private $client;

    public function __construct(RabbitMqClient $client)
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
