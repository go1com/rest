<?php

namespace go1\rest\tests;

class FoodCreatedEvent
{
    const NAME = 'foo.create';

    public $name;

    public static function create(\stdClass $raw):self
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
        ($app = $this->app())
            ->stream()
            ->on(FoodCreatedEvent::NAME, function (FoodCreatedEvent $event) {
                $this->assertEquals('Ant', $event->name);
            });

        $app->stream()->commit(FoodCreatedEvent::NAME, (object)['name' => 'Ant']);
    }
}
