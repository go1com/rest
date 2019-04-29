<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\request\MessagePublisher;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use ReflectionObject;

class MessagePublisherTest extends RestTestCase
{
    private function httpClient(Psr17Factory $mf): ClientInterface
    {
        $client = $this
            ->getMockBuilder(ClientInterface::class)
            ->setMethods(['sendRequest'])
            ->getMock();

        $client
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturnCallback(
                function (RequestInterface $req) use ($mf) {
                    $req->getBody()->rewind();
                    $_ = $req->getBody()->getContents();
                    $_ = json_decode($_);

                    $this->assertEquals($_->routingKey, 'user.login');
                    $this->assertEquals($_->body, '{"mail": "hi@qa.com", "password": "112233!#%"}');
                    $this->assertEquals($_->context->time, 'now');

                    return $mf->createResponse(204);
                }
            );

        return $client;
    }

    public function test()
    {
        $publisher = $this->get(MessagePublisher::class);
        $rb = new ReflectionObject($publisher);
        $rp = $rb->getProperty('client');
        $rp->setAccessible(true);
        $rp->setValue($publisher, $this->httpClient($this->get(Psr17Factory::class)));

        $res = $publisher->publish(
            '/consume',
            'user.login',
            '{"mail": "hi@qa.com", "password": "112233!#%"}',
            ['time' => 'now']
        );

        $this->assertEquals(204, $res->getStatusCode());
    }
}
