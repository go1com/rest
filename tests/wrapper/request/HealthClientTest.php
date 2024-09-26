<?php

namespace go1\rest\tests\wrapper\request;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use go1\rest\tests\RestTestCase;
use Slim\Exception\HttpNotFoundException;

class HealthClientTest extends RestTestCase
{
    public function test()
    {
		$this->expectException(NoNodesAvailableException::class);
		$this->expectExceptionCode(0);
		$this->expectExceptionMessage('No alive nodes found in your cluster');

        $req = $this->mf()->createRequest('GET', '/healthz');
		$this->rest()->process($req, $this->mf()->createResponse());
    }
}
