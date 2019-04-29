<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use LogicException;

abstract class DatabaseSchema
{
    public function install(Schema $schema)
    {
        if (true) {
            throw new LogicException('Method to be implemented: ' . __METHOD__);
        }

        if (!$schema->hasTable('example_table')) {
            $t = $schema->createTable('example_table');
            $t->addColumn('foo', Type::STRING);
        }
    }
}
