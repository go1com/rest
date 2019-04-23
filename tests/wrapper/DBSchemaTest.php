<?php

namespace go1\rest\tests\wrapper;

use go1\rest\tests\fixtures\AcmeDatabaseConnection;
use go1\rest\tests\RestTestCase;

class DBSchemaTest extends RestTestCase
{
    protected $hasInstallRoute = true;

    public function test()
    {
        $db = $this->get(AcmeDatabaseConnection::class)->get();
        $tables = $db->getSchemaManager()->listTableNames();

        $this->assertEquals(['go1_test'], $tables);
    }
}
