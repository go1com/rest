<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Connection;
use DomainException;
use function get_class;

abstract class DatabaseConnection
{
    const DB_NAME = '';

    private $dbs;

    public function __construct(DatabaseConnections $dbs)
    {
        $this->dbs = $dbs;
    }

    public function get(): Connection
    {
        if (!static::DB_NAME) {
            throw new DomainException('Constant is not defined: %s::DB_NAME', get_class($this));
        }

        return $this->dbs->get(static::DB_NAME);
    }
}
