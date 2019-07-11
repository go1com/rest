<?php

namespace go1\rest\tests;

use go1\rest\RestService;
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
                 function (FoodCreatedEvent $event, array $context) {
                     $this->assertEquals('Ant', $event->name);
                     $this->assertEquals('bar', $context['foo']);
                 }
             )
             ->commit($event = FoodCreatedEvent::NAME, $payload = '{"name": "Ant"}', ['foo' => 'bar']);

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
        $req = $this
            ->mf()
            ->createRequest('POST', '/consume')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . RestService::SYSTEM_USER)
            ->withBody(
                $this->mf()->streamFactory()->createStream(json_encode([
                    'routingKey' => $event = 'foo.create',
                    'body'       => $payload = ['name' => 'Ant'],
                ]))
            );

        $res = $this->rest()->process($req, $this->mf()->createResponse());

        $this->assertEquals(204, $res->getStatusCode());
        $this->assertTrue(count($this->committed[$event]) >= 1);
        $this->assertEquals(json_encode($payload), $this->committed[$event][0][0]);
    }

    public function testConsumeWithBadBody()
    {
        $req = $this
            ->mf()
            ->createRequest('POST', '/consume')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Authorization', 'Bearer ' . RestService::SYSTEM_USER)
            ->withBody($this->mf()->streamFactory()->createStream('{}'));
        $res = $this->rest()->process($req, $this->mf()->createResponse());

        $this->assertEquals(400, $res->getStatusCode());
    }

    public function testHandleWithScalarArgument()
    {
        $log = [];

        $stream = $this->stream();
        $stream->on('echo_str', '', function (string $_) use (&$log) { $log[] = $_; });
        $stream->on('echo_int', '', function (int $_) use (&$log) { $log[] = $_; });
        $stream->on('echo_flt', '', function (float $_) use (&$log) { $log[] = $_; });
        $stream->on('echo_bool', '', function (bool $_) use (&$log) { $log[] = $_; });
        $stream->commit('echo_str', $expect[] = 'Hi there');
        $stream->commit('echo_int', $expect[] = 5);
        $stream->commit('echo_flt', $expect[] = 3.33);
        $stream->commit('echo_bool', $expect[] = false);

        $this->assertEquals($expect[0], $log[0], 'Logged: Hi there');
        $this->assertEquals($expect[1], $log[1], 'Logged: 5');
        $this->assertEquals($expect[2], $log[2], 'Logged: 3.33');
        $this->assertEquals($expect[3], $log[3], 'Logged: false');
    }

    public function testPostConsume403()
    {
        $req = $this
            ->mf()
            ->createRequest('POST', '/consume')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                $this->mf()->streamFactory()->createStream(json_encode([
                    'routingKey' => $event = 'foo.create',
                    'body'       => $payload = ['name' => 'Ant'],
                ]))
            );

        $res = $this->rest()->process($req, $this->mf()->createResponse());

        $this->assertEquals(403, $res->getStatusCode());
    }
}
