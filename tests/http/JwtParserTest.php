<?php

namespace go1\rest\tests\http;

use go1\rest\Request;
use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\tests\RestTestCase;

class JwtParserTest extends RestTestCase
{
    protected $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJnbzEudXNlciIsInZlciI6InYxOC4xMi4wMy4wIiwiZXhwIjoxNTU0Mzk1OTAzLCJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjo0MTY2OCwiaW5zdGFuY2UiOiJhY2NvdW50cy1kZXYuZ29jYXRhbHl6ZS5jb20iLCJwcm9maWxlX2lkIjoxNjY5NiwibWFpbCI6InBodW9uZy5odXluaEBnbzEuY29tIiwibmFtZSI6IlBodW9uZyBIdXluaCIsInJvbGVzIjpbIkFkbWluIG9uICNBY2NvdW50cyIsImRldmVsb3BlciIsInRyYWluaW5nIGFjY291bnQgbWFuYWdlciJdLCJhY2NvdW50cyI6W3siaWQiOjQ4NzU3LCJpbnN0YW5jZSI6InFhLm15Z28xLmNvbSIsInBvcnRhbF9pZCI6NTAwNDcwLCJwcm9maWxlX2lkIjoxMywicm9sZXMiOlsiYWRtaW5pc3RyYXRvciIsIlN0dWRlbnQiXX1dfX19.q0kLPi9ynbQJQTOw5IMtDhj4h4nA2u1eowZbIkkE-uw';

    protected function install(RestService $rest)
    {
        parent::install($rest);

        $rest->get('/auth', function (Request $request, Response $response) {
            return $response->withJson($request->contextUser());
        });
    }

    public function testCanParseQuery()
    {
        $req = $this->mf()->createRequest('GET', '/auth?jwt=' . $this->jwt);
        $res = $this->rest()->process($req, new Response());
        $payload = $res->json();

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('phuong.huynh@go1.com', $payload->mail);
    }

    public function testCanParseHeader()
    {
        $req = $this
            ->mf()
            ->createRequest('GET', '/auth')
            ->withHeader('authorization', 'Bearer ' . $this->jwt);
        $res = $this->rest()->process($req, new Response());

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('phuong.huynh@go1.com', $res->json()->mail);
    }
}
