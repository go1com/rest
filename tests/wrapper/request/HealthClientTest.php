<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;

class HealthClientTest extends RestTestCase
{
    public function test()
    {
        $req = $this->mf()->createRequest('GET', '/healthz');
        $res = $this->rest()->process($req, $this->mf()->createResponse());
        $results = $res->json(true);

        $this->assertEquals(500, $res->getStatusCode());
        $this->assertEquals(true, $results['db.acme']['ping']);
        $this->assertEquals(false, $results['elasticsearch.default']['ping'], 'No elastic server.');
    }
}
