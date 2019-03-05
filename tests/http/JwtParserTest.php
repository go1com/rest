<?php

namespace go1\rest\tests\http;

use go1\rest\Request;
use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\tests\RestTestCase;

class JwtParserTest extends RestTestCase
{
    /**
     * @var RestService
     */
    protected $app;

    protected $jwt =
        'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJnbzEudXNlciIsInZlciI6In
        YxOC4xMi4wMy4wIiwiZXhwIjoxNTU0Mzk1OTAzLCJvYmplY3QiOnsidHlwZSI6InVzZXIiLC
        Jjb250ZW50Ijp7ImlkIjo0MTY2OCwiaW5zdGFuY2UiOiJhY2NvdW50cy1kZXYuZ29jYXRhbH
        l6ZS5jb20iLCJwcm9maWxlX2lkIjoxNjY5NiwibWFpbCI6InBodW9uZy5odXluaEBnbzEuY2
        9tIiwibmFtZSI6IlBodW9uZyBIdXluaCIsInJvbGVzIjpbIkFkbWluIG9uICNBY2NvdW50cy
        IsImRldmVsb3BlciIsInRyYWluaW5nIGFjY291bnQgbWFuYWdlciJdLCJhY2NvdW50cyI6W3
        siaWQiOjQ4NzU3LCJpbnN0YW5jZSI6InFhLm15Z28xLmNvbSIsInBvcnRhbF9pZCI6NTAwND
        cwLCJwcm9maWxlX2lkIjoxMywicm9sZXMiOlsiYWRtaW5pc3RyYXRvciIsIlN0dWRlbnQiXX
        1dfX19.q0kLPi9ynbQJQTOw5IMtDhj4h4nA2u1eowZbIkkE-uw';

    protected function server()
    {
        $this->app->get('/auth', function (Request $request, Response $response) {
            return $response->withJson($request->contextUser());
        });
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->rest();
        $this->server();
    }

    public function testCanParseQuery()
    {
        $req = $this->mf()->createRequest('GET', '/auth?jwt=' . $this->jwt);
        $res = $this->app->process($req, new Response());
        $payload = json_decode($res->getBody());

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('phuong.huynh@go1.com', $payload->mail);
    }

    public function testCanParseHeader()
    {
        $req = $this
            ->mf()
            ->createRequest('GET', '/auth')
            ->withHeader('authorization', 'Bearer ' . $this->jwt);
        $res = $this->app->process($req, new Response());
        $payload = json_decode($res->getBody());

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('phuong.huynh@go1.com', $payload->mail);
    }
}
