<?php

namespace go1\rest\tests\fixtures;

use go1\rest\Request;
use go1\rest\Response;

class UserCreateController
{
    public function post(Request $request, Response $response)
    {
        return $response->withJson(['payload' => serialize($request->getAttribute(User::class))]);
    }
}
