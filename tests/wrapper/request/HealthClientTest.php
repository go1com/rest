<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;
use Slim\Exception\HttpNotFoundException;

class HealthClientTest extends RestTestCase
{
    public function test()
    {
		$this->expectException(HttpNotFoundException::class);
		$this->expectExceptionCode(404);
		$this->expectExceptionMessage('Not found');

        $req = $this->mf()->createRequest('GET', '/healthz');
        $this->rest()->process($req, $this->mf()->createResponse());
    }
}
