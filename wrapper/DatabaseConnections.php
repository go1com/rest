<?php

namespace go1\rest\wrapper;

use DI\Container;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DatabaseConnections
{
    private $dbOptions;

    public function __construct(Container $c)
    {
        $this->dbOptions = $c->get('dbOptions');
    }

    public function get($name): Connection
    {
        return DriverManager::getConnection($this->dbOptions[$name]);
    }
}
