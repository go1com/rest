<?php

namespace go1\rest\wrapper;

use go1\rest\Request;
use go1\rest\Response;
use Http\Message\MessageFactory as TheInterface;
use Http\Message\StreamFactory\SlimStreamFactory;
use Http\Message\UriFactory\SlimUriFactory;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;

/**
 * Creates Slim 3 messages.
 *
 * @author Mika Tuupola <tuupola@appelsiini.net>
 */
final class MessageFactory implements TheInterface
{
    /**
     * @var SlimStreamFactory
     */
    private $streamFactory;

    /**
     * @var SlimUriFactory
     */
    private $uriFactory;

    public function __construct()
    {
        $this->streamFactory = new SlimStreamFactory();
        $this->uriFactory = new SlimUriFactory();
    }

    public function streamFactory()
    {
        return $this->streamFactory;
    }


    /**
     * @return Request|ServerRequestInterface
     */
    public function createRequest(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    )
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

    /**
     * {@inheritdoc}
     */
    public function createResponse(
        $statusCode = 200,
        $reasonPhrase = null,
        array $headers = [],
        $body = null,
        $protocolVersion = '1.1'
    )
    {
        return (new Response(
            $statusCode,
            new Headers($headers),
            $this->streamFactory->createStream($body)
        ))->withProtocolVersion($protocolVersion);
    }
}
