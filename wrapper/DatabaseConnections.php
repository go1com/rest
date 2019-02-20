<?php

namespace go1\rest\wrapper;

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DatabaseConnections
{
    private $options;

    public function __construct(Container $c)
    {
        $this->options = $c->get('dbOptions');
    }

    public function get($name): Connection
    {
        return DriverManager::getConnection($this->options[$name]);
    }
}
