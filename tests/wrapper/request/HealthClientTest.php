<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;

class HealthClientTest extends RestTestCase
{
    public function test()
    {
        $req = $this->mf()->createRequest('GET', '/healthz');
        $res = $this->rest()->process($req, $this->mf()->createResponse());

        $this->assertEquals(200, $res->getStatusCode());
    }
}
