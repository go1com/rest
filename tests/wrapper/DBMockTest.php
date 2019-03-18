<?php

namespace go1\rest\tests\wrapper;

use Doctrine\DBAL\Schema\Schema;
use go1\rest\RestService;
use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\DatabaseConnections;

class DBMockTest extends RestTestCase
{
    protected function install(RestService $rest)
    {
        parent::install($rest);

        $schemaInstall = function (Schema $schema) {
            if (!$schema->hasTable('go1_test')) {
                $table = $schema->createTable('go1_test');
                $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
                $table->addColumn('note', 'text');
                $table->setPrimaryKey(['id']);
            }
        };
        $db = $rest->getContainer()->get(DatabaseConnections::class)->get('default');
        DatabaseConnections::install($db, [$schemaInstall]);
    }

    public function testInsertData()
    {
        $rest = $this->rest();
        $db = $rest->getContainer()->get(DatabaseConnections::class)->get('default');
        $db->insert('go1_test', $expected = ['note' => 'some testing']);

        $row = $db->fetchAssoc('SELECT * FROM go1_test');
        $this->assertNotEmpty($row);
        $this->assertEquals($expected['note'], $row['note']);
    }
}
