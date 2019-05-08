<?php

namespace go1\rest\wrapper\request;

use go1\rest\wrapper\ServiceUrl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Example:
 *
 *      $client = $container->get(HttpClient::class);
 *      $client->sendRequest(
 *          $client
 *              ->createRequest(
 *                  'POST',
 *                  $client
 *                      ->serviceUr('user', '/login')
 *                      ->withQuery('csrf-token', 'some-thing')
 *              )
 *              ->withBody($client->createStream('{"username": "my-name", "password": "my-password"}'))
 *      );
 */
class Http
{
    private $client;
    private $sf;
    private $serviceUrl;

    public function __construct(ClientInterface $client, Psr17Factory $sf, ServiceUrl $serviceUrl)
    {
        $this->client = $client;
        $this->sf = $sf;
        $this->serviceUrl = $serviceUrl;
    }

    /**
     * @param string              $method
     * @param string|UriInterface $uri
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->sf->createRequest($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->sf->createResponse($code, $reasonPhrase);
    }

    public function createStream(string $content): StreamInterface
    {
        return $this->sf->createStream($content);
    }

    public function sendRequest(RequestInterface $req): ResponseInterface
    {
        return $this->client->sendRequest($req);
    }

    public function serviceUri(string $service, string $path = ''): UriInterface
    {
        return $this->sf->createUri(
            $this->serviceUrl->get($service, $path)
        );
    }
}
