<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use function call_user_func;

class DatabaseInstaller
{
    private $defer = [];

    public function install(Connection $db, callable $callback)
    {
        $compare = new Comparator;
        $schemaManager = $db->getSchemaManager();
        $schema = $schemaManager->createSchema();
        $originSchema = clone $schema;
        call_user_func($callback, $schema, $this);
        $diff = $compare->compare($originSchema, $schema);
        foreach ($diff->toSql($db->getDatabasePlatform()) as $sql) {
            try {
                $db->executeQuery($sql);
            } catch (TableExistsException $e) {
                # table already created before, no worry the error
            }
        }
    }

    public function defer(callable $defer)
    {
        $this->defer[] = $defer;
    }

    public function callDefer()
    {
        foreach ($this->defer as $defer) {
            $defer();
        }

        $this->defer = [];
    }
}
