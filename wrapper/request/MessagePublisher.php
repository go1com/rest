<?php

namespace go1\rest\wrapper\request;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class MessagePublisher
{
    private $client;
    private $sf;

    public function __construct(ClientInterface $client, Psr17Factory $mf)
    {
        $this->client = $client;
        $this->sf = $mf;
    }

    public function publish(string $path, string $routingKey, string $body, array $context): ResponseInterface
    {
        $req = [];
        $req['routingKey'] = $routingKey;
        $req['body'] = $body;
        $req['context'] = $context;
        $req = json_encode($req);
        $req = $this->sf->createStream($req);
        $req = $this->sf
            ->createServerRequest('POST', $path)
            ->withBody($req);

        return $this->client->sendRequest($req);
    }
}
