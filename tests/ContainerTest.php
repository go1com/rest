<?php

namespace go1\rest\tests;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class ContainerTest extends RestTestCase
{
    public function test()
    {
        $c = $this->rest()->getContainer();

        $this->assertTrue($c->get(ClientInterface::class) instanceof ClientInterface);
        $this->assertTrue($c->get(LoggerInterface::class) instanceof LoggerInterface);
    }
}
