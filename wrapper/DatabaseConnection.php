<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Connection;
use DomainException;
use function sprintf;

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
            $err = sprintf('Constant is not defined: %s::DB_NAME', __CLASS__);
            throw new DomainException($err);
        }
    }

    protected static function dbName(): string
    {
        return static::DB_NAME;
    }

    public function get(): Connection
    {
        static::check();

        return $this->dbs->get(static::DB_NAME);
    }

    public static function readConnectionOptions()
    {
        static::check();

        return DatabaseConnections::connectionOptions(static::dbName(), DatabaseConnections::CON_OPTION_DISABLE_MASTER, static::driver());
    }

    public static function writeConnectionOptions()
    {
        static::check();

        return DatabaseConnections::connectionOptions(static::dbName(), DatabaseConnections::CON_OPTION_ALWAYS_MASTER, static::driver());
    }

    public static function connectionOptions()
    {
        static::check();

        return DatabaseConnections::connectionOptions(static::dbName(), DatabaseConnections::CON_OPTION_AUTO_MASTER, static::driver());
    }

    protected static function driver(): string
    {
        return 'pdo_mysql';
    }
}
