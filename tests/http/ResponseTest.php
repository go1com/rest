<?php

namespace go1\rest\tests\http;

use go1\rest\Response;
use go1\rest\tests\RestTestCase;

class ResponseTest extends RestTestCase
{
    public function testBodyString()
    {
        /** @var Response $res */
        $res = $this->mf()->createResponse(200, null, [], $bodyString = '{"foo": "bar"}');
        $this->assertEquals($bodyString, $res->bodyString());
    }

    public function testJson()
    {
        /** @var Response $res */
        $res = $this->mf()->createResponse(200, null, [], $bodyString = '{"foo": "bar"}');
        $this->assertEquals("bar", $res->json()->foo);
    }
}
