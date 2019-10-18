<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;
use go1\rest\tests\traits\ReflectionTrait;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\Chunk\DataChunk;
use Symfony\Contracts\HttpClient\ChunkInterface;

class HttpClientTest extends RestTestCase
{
    use ReflectionTrait;

    public function testForwardRequestId()
    {
        $rest = $this->rest();
        $_SERVER['HTTP_X_REQUEST_ID'] = $requestId = 'abcd-1234';

        $client = $this->getObjectProperty($rest->getContainer()->get(ClientInterface::class), 'client');
        $options = $this->getObjectProperty($client, 'defaultOptions');

        if (isset($options['normalized_headers'])) {
            $this->assertEquals('X-Request-Id: abcd-1234', $options['normalized_headers']['x-request-id'][0]);
        }

        if (isset($options['headers'])) {
            $this->assertEquals($requestId, $options['headers']['x-request-id'][0]);
        }
    }

    public function testContract()
    {
        $chunk = new DataChunk;
        $this->assertTrue($chunk instanceof ChunkInterface);
    }
}
