<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Connection;
use DomainException;

abstract class DatabaseConnection
{
    const DB_NAME = '';

    private $dbs;

    public function __construct(DatabaseConnections $dbs)
    {
        $this->dbs = $dbs;
    }

    private static function check()
    {
        if (!static::DB_NAME) {
            throw new DomainException('Constant is not defined: %s::DB_NAME', __CLASS__);
        }
    }

    public function get(): Connection
    {
        static::check();

        return $this->dbs->get(static::DB_NAME);
    }

    public static function connectionOptions()
    {
        static::check();

        return DatabaseConnections::connectionOptions(static::DB_NAME);
    }
}
