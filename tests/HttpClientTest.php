<?php

namespace go1\rest\tests;

use Psr\Http\Client\ClientInterface;

class HttpClientTest extends RestTestCase
{
    public function test()
    {
        $rest = $this->rest();
        
        # Use container getter
        $this->assertTrue($rest->getContainer()->get(ClientInterface::class) instanceof ClientInterface);
    }
}
