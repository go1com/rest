<?php

namespace go1\rest\tests\fixtures;

/**
 * @property int    $id
 * @property int    $portalId
 * @property string $mail
 * @property int    $status
 */
class User
{
    protected $id;
    protected $portalId;
    protected $mail;
    protected $status;

    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->{$name} : null;
    }
}
