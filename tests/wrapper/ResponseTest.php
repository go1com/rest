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

    public function testError()
    {
        $rest = $this->rest();
        $rest->get('/error', function () {
            throw new InternalResourceError('Just for test');
        });

        $request = $this->mf()->createRequest('GET', '/error');
        $response = $this->mf()->createResponse();
        $response = $rest->process($request, $response);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('Just for test', $response->json()->errors[0]->title);
    }

    public function testGetSwagger()
    {
        $rest = $this->rest();
        $request = $this->mf()->createRequest('GET', '/swagger');
        $response = $this->mf()->createResponse();
        $response = $rest->process($request, $response);
        $swagger = $response->json(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('loId', $swagger['paths']['/lo/{portalId}/{loId}/learners/{keyword}']['GET']['parameters'][0]['name']);
    }
}
