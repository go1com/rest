<?php

namespace go1\rest\tests;

use go1\rest\tests\fixtures\FoodCreatedEvent;

class StreamTest extends RestTestCase
{
    public function test()
    {
        $this->rest()->stream()
            ->on(
                FoodCreatedEvent::NAME,
                function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
            )
            ->commit($event = FoodCreatedEvent::NAME, $payload = '{"name": "Ant"}');

        # Test cases can easily checking what event was committed
        $this->assertTrue(1 == count($this->committed[$event]));
        $this->assertEquals($payload, $this->committed[$event][0][0]);
    }
}
