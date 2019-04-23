<?php

namespace go1\rest\tests\wrapper;

use go1\rest\tests\fixtures\AcmeDatabaseConnection;
use go1\rest\tests\RestTestCase;

class DBMockTest extends RestTestCase
{
    protected $hasInstallRoute = true;

    public function testInsertData()
    {
        $rest = $this->rest();
        $db = $rest->getContainer()->get(AcmeDatabaseConnection::class)->get();
        $db->insert('go1_test', $expected = ['note' => 'some testing']);

        $row = $db->fetchAssoc('SELECT * FROM go1_test');
        $this->assertNotEmpty($row);
        $this->assertEquals($expected['note'], $row['note']);
    }
}
