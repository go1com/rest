<?php

namespace go1\rest\tests\wrapper\request;

use go1\rest\tests\RestTestCase;

class RequestTest extends RestTestCase
{
    public function testBodyString()
    {
        $req = $this->mf()->createRequest('POST', '/', [], json_encode(['foo' => 'bar']));
        $this->assertEquals(json_encode(['foo' => 'bar']), $req->bodyString());
    }

    public function testJsonArray()
    {
        $req = $this->mf()->createRequest('POST', '/', [], json_encode(['foo' => 'bar']));
        $this->assertEquals('bar', $req->json()['foo']);
    }

    public function testJsonObject()
    {
        $req = $this->mf()->createRequest('POST', '/', [], json_encode(['foo' => 'bar']));
        $this->assertEquals('bar', $req->json(false)->foo);
    }

    public function testEmptyBody()
    {
        $req = $this->mf()->createRequest('POST', '/', [], '');
        $this->assertEmpty($req->json(false));
    }
}
