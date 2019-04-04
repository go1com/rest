<?php

namespace go1\rest\tests;

use go1\rest\tests\fixtures\FoodCreatedEvent;
use function json_encode;

class StreamTest extends RestTestCase
{
    public function test()
    {
        $this->stream()
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
        $this->stream()
             ->on(
                 FoodCreatedEvent::NAME,
                 'Notification',
                 function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
             );

        $req = $this->mf()->createRequest('GET', '/consume');
        $res = $this->rest()->process($req, $this->mf()->createResponse());
        $json = json_decode(json_encode($res->json()), true);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('Notification', $json[FoodCreatedEvent::NAME]);
    }

    public function testPostConsume()
    {
        $this->stream()
             ->on(
                 FoodCreatedEvent::NAME,
                 'Notification',
                 function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
             );

        $res = $this->rest()->process(
            $this->mf()
                 ->createRequest('POST', '/consume')
                 ->withHeader('Content-Type', 'application/json')
                 ->withBody(
                     $this->mf()
                          ->streamFactory()
                          ->createStream(json_encode(
                              [
                                  'routingKey' => $event = 'foo.create',
                                  'body'       => $payload = ['name' => 'Ant'],
                              ]
                          ))
                 ),
            $this->mf()->createResponse()
        );

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertTrue(1 == count($this->committed[$event]));
        $this->assertEquals(json_encode($payload), $this->committed[$event][0][0]);
    }

    public function testConsumeWithBadBody()
    {
        $res = $this->rest()->process(
            $this->mf()
                 ->createRequest('POST', '/consume')
                 ->withHeader('Content-Type', 'application/json')
                 ->withBody(
                     $this->mf()
                          ->streamFactory()
                          ->createStream('bad payload')
                 ),
            $this->mf()->createResponse()
        );

        $this->assertEquals(400, $res->getStatusCode());
    }
}
