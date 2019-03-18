<?php

namespace go1\rest\tests\http;

use go1\rest\tests\RestTestCase;

class RequestTest extends RestTestCase
{
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
}
