<?php

namespace go1\rest\tests;

use stdClass;

class FoodCreatedEvent
{
    const NAME = 'foo.create';

    public $name;

    public static function create(stdClass $raw): self
    {
        $_ = new self;
        $_->name = $raw->name;

        return $_;
    }
}

class StreamTest extends RestTestCase
{
    public function test()
    {
        $stream = $this->app()->stream();

        $stream
            ->on(
                FoodCreatedEvent::NAME,
                function (FoodCreatedEvent $event) { $this->assertEquals('Ant', $event->name); }
            )
            ->commit(FoodCreatedEvent::NAME, (object) ['name' => 'Ant']);
    }
}
