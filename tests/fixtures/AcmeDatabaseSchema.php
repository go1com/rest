<?php

namespace go1\rest\tests\fixtures;

use Doctrine\DBAL\Schema\Schema;
use go1\rest\wrapper\DatabaseSchema;

class AcmeDatabaseSchema extends DatabaseSchema
{
    public function install(Schema $schema)
    {
        if (!$schema->hasTable('go1_test')) {
            $this->tableAcme($schema);
        }
    }

    private function tableAcme(Schema $schema)
    {
        $t = $schema->createTable('go1_test');
        $t->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $t->addColumn('note', 'text');
        $t->setPrimaryKey(['id']);
    }
}
