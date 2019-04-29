<?php

namespace go1\rest\tests\fixtures;

class FoodCreatedEvent
{
    const NAME = 'foo.create';

    public $name;

    public static function create(string $payload): self
    {
        $_ = new self;
        foreach (json_decode($payload) as $k => $v) {
            $_->{$k} = $v;
        }

        return $_;
    }
}
