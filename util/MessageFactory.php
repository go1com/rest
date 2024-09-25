<?php

namespace go1\rest\util;

use go1\rest\Request;
use go1\rest\Response;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Headers;

final class MessageFactory
{
    private StreamFactory $streamFactory;

    private UriFactory $uriFactory;

    public function __construct()
    {
		$this->streamFactory = new StreamFactory();
		$this->uriFactory = new UriFactory();
    }

    public function streamFactory()
    {
        return $this->streamFactory;
    }

    public function uriFactory()
    {
        return $this->uriFactory;
    }

    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = '',
        $protocolVersion = '1.1'
    ): Request|ServerRequestInterface
    {
        return (new Request(
            $method,
            $this->uriFactory->createUri($uri),
            new Headers($headers),
            [],
            [],
            $this->streamFactory->createStream($body),
            []
        ))->withProtocolVersion($protocolVersion);
    }

    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        array $headers = [],
        $body = '',
        $protocolVersion = '1.1'
    ): Response
    {
        return (new Response(
            $statusCode,
            new Headers($headers),
            $this->streamFactory->createStream($body)
        ))->withProtocolVersion($protocolVersion);
    }
}
