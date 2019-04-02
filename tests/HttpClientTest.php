<?php

namespace go1\rest\tests;

use Psr\Http\Client\ClientInterface;

class HttpClientTest extends RestTestCase
{
    public function test()
    {
        $rest = $this->rest();
        $httpClient = $rest->httpClient();

        $this->assertTrue($httpClient instanceof ClientInterface);
    }
}
