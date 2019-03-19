<?php

namespace go1\rest\tests\middleware;

use go1\rest\tests\fixtures\User;
use go1\rest\tests\RestTestCase;

class JsonSchemaValidatorMiddleWareTest extends RestTestCase
{
    public function test()
    {
        $rest = $this->rest();
        $req = $this->mf()->createRequest('POST', '/user', [], json_encode($data = [
            'id'       => 3,
            'mail'     => 'actor@mail.com',
            'portalId' => 2,
            'status'   => 1,
        ]));

        $res = $rest->process($req, $this->mf()->createResponse());
        $this->assertEquals(200, $res->getStatusCode());

        $user = unserialize($res->json()->payload);
        $this->assertTrue($user instanceof User);
        $this->assertEquals($data['id'], $user->id);
        $this->assertEquals($data['mail'], $user->mail);
        $this->assertEquals($data['portalId'], $user->portalId);
        $this->assertEquals($data['status'], $user->status);
    }
}
