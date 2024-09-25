<?php

namespace go1\rest\tests\wrapper;

use go1\rest\errors\InternalResourceError;
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

    public function testGetSwagger()
    {
        $rest = $this->rest();
        $request = $this->mf()->createRequest('GET', '/api');
        $res = $this->mf()->createResponse();
        $res = $rest->process($request, $res);
        $api = $res->json(true);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('loId', $api['paths']['/lo/{portalId}/{loId}/learners/{keyword}']['get']['parameters'][1]['name']);
    }
}
