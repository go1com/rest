<?php

namespace go1\rest\tests;

use go1\rest\Request;
use go1\rest\tests\fixtures\FoodCreatedEvent;
use Http\Message\StreamFactory\SlimStreamFactory;

class StreamTest extends RestTestCase
{
    public function test()
    {
        $this->rest()->stream()
            ->on(
                FoodCreatedEvent::NAME,
                'Demo',
                function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
            )
            ->commit($event = FoodCreatedEvent::NAME, $payload = '{"name": "Ant"}');

        # Test cases can easily checking what event was committed
        $this->assertTrue(1 == count($this->committed[$event]));
        $this->assertEquals($payload, $this->committed[$event][0][0]);
    }

    public function testGetConsume()
    {
        ($rest = $this->rest())
            ->stream()
            ->on(
                FoodCreatedEvent::NAME,
                'Notification',
                function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
            );

        $req = $this->mf()->createRequest('GET', '/consume');
        $res = $rest->process($req, $this->mf()->createResponse());
        $json = json_decode(json_encode($res->json()), true);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Notification', $json[FoodCreatedEvent::NAME]);
    }

    public function testPostConsume()
    {
        ($rest = $this->rest())
            ->stream()
            ->on(
                FoodCreatedEvent::NAME,
                'Notification',
                function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
            );

        /**
         * @var $req Request
         */
        $req = $this->mf()
            ->createRequest('POST', '/consume')
            ->withHeader('Content-Type', 'application/json')
            ->withBody((new SlimStreamFactory)->createStream(json_encode([
                'routingKey' => $event = 'foo.create',
                'body'       => $payload = ['name' => 'Ant'],
            ])));
        $res = $rest->process($req, $this->mf()->createResponse());

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertTrue(1 == count($this->committed[$event]));
        $this->assertEquals(json_encode($payload), $this->committed[$event][0][0]);
    }

    public function testConsumeWithBadBody()
    {
        /**
         * @var $req Request
         */
        $req = $this->mf()
            ->createRequest('POST', '/consume')
            ->withHeader('Content-Type', 'application/json')
            ->withBody((new SlimStreamFactory)->createStream("bad payload"));
        $res = $this->rest()->process($req, $this->mf()->createResponse());

        $this->assertEquals(400, $res->getStatusCode());
    }
}
