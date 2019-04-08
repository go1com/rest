<?php

namespace go1\rest\tests\http;

use go1\rest\Request;
use go1\rest\Response;
use go1\rest\tests\fixtures\FoodCreatedEvent;
use go1\rest\tests\RestTestCase;
use go1\rest\tests\traits\ReflectionTrait;
use function json_encode;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class HttpClientTest extends RestTestCase
{
    use ReflectionTrait;

    public function testForwardRequestId()
    {
        $rest = $this->rest();
        $_SERVER['HTTP_X_REQUEST_ID'] = $requestId = 'abcd-1234';

        $client = $this->getObjectProperty($rest->getContainer()->get(ClientInterface::class), 'client');
        $options = $this->getObjectProperty($client, 'defaultOptions');;

        $this->assertEquals($requestId, $options['headers']['x-request-id'][0]);
    }
}
