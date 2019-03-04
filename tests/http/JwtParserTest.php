<?php

namespace go1\rest\tests\http;

use go1\rest\Response;
use go1\rest\RestService;
use go1\rest\tests\RestTestCase;
use Slim\Http\Environment;
use go1\rest\Request;

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
        siaWQiOjQ4NzU3LCJpbnN0YW5jZSI6InZpbWFjZGV2MjIwNS5teWdvMS5jb20iLCJwb3J0YW
        xfaWQiOjUwMDQ3MCwicHJvZmlsZV9pZCI6MTMsInJvbGVzIjpbImFkbWluaXN0cmF0b3IiLC
        JTdHVkZW50Il19LHsiaWQiOjQ4NzYwLCJpbnN0YW5jZSI6InBodW9uZy5teWdvMS5jb20iLC
        Jwb3J0YWxfaWQiOjM2Mjg1MiwicHJvZmlsZV9pZCI6MSwicm9sZXMiOlsiYWRtaW5pc3RyYX
        RvciIsIlN0dWRlbnQiXX0seyJpZCI6NTE3NTYsImluc3RhbmNlIjoicGh1b25naHV5bmgubX
        lnbzEuY29tIiwicG9ydGFsX2lkIjo1MDYyMzYsInByb2ZpbGVfaWQiOjEsInJvbGVzIjpbIl
        N0dWRlbnQiLCJhZG1pbmlzdHJhdG9yIl19LHsiaWQiOjY1MTU5LCJpbnN0YW5jZSI6InBodW
        9uZ2gubXlnbzEuY29tIiwicG9ydGFsX2lkIjo2Mjg5ODQsInByb2ZpbGVfaWQiOjEsInJvbG
        VzIjpbIlN0dWRlbnQiLCJhZG1pbmlzdHJhdG9yIl19LHsiaWQiOjY1MTY4LCJpbnN0YW5jZS
        I6InBodW9uZ2gxLm15Z28xLmNvbSIsInBvcnRhbF9pZCI6NjI5MDQwLCJwcm9maWxlX2lkIj
        oxLCJyb2xlcyI6WyJTdHVkZW50IiwiYWRtaW5pc3RyYXRvciJdfSx7ImlkIjo2NTE3MywiaW
        5zdGFuY2UiOiJwaHVvbmdoMi5teWdvMS5jb20iLCJwb3J0YWxfaWQiOjYyOTA0MSwicHJvZm
        lsZV9pZCI6MSwicm9sZXMiOlsiU3R1ZGVudCIsImFkbWluaXN0cmF0b3IiXX0seyJpZCI6Nj
        Y5NzMsImluc3RhbmNlIjoicGh1b25nNS5teWdvMS5jb20iLCJwb3J0YWxfaWQiOjY1MDU1MS
        wicHJvZmlsZV9pZCI6MSwicm9sZXMiOlsiU3R1ZGVudCIsImFkbWluaXN0cmF0b3IiXX0sey
        JpZCI6NzQ2ODEsImluc3RhbmNlIjoiZ29jbG91ZC5teWdvMS5jb20iLCJwb3J0YWxfaWQiOj
        c0MjQxMiwicHJvZmlsZV9pZCI6MSwicm9sZXMiOlsiU3R1ZGVudCIsImFkbWluaXN0cmF0b3
        IiXX0seyJpZCI6NzQ4OTksImluc3RhbmNlIjoiMjAxOHNpZGV2Lm15Z28xLmNvbSIsInBvcn
        RhbF9pZCI6NTAwNTkyLCJwcm9maWxlX2lkIjo3MCwicm9sZXMiOlsiYWRtaW5pc3RyYXRvci
        IsIlN0dWRlbnQiXX0seyJpZCI6NzQ5MDAsImluc3RhbmNlIjoiZGV2dG9nby5teWdvMS5jb2
        0iLCJwb3J0YWxfaWQiOjc0NDYxNCwicHJvZmlsZV9pZCI6MSwicm9sZXMiOlsiU3R1ZGVudC
        IsImFkbWluaXN0cmF0b3IiXX0seyJpZCI6OTIxMDQsImluc3RhbmNlIjoidGVzdHBvcnRhbD
        EubXlnbzEuY29tIiwicG9ydGFsX2lkIjo4NDYzNTEsInByb2ZpbGVfaWQiOjEsInJvbGVzIj
        pbIlN0dWRlbnQiLCJhZG1pbmlzdHJhdG9yIl19XX19fQ.MOER3kQ4EFP1pub3ArbtO7B6rJ9
        eWWaYh08jhkAnI28';

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
        $req = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/auth',
            'QUERY_STRING' => http_build_query(['jwt' => $this->jwt]),
        ]));
        $resp = $this->app->process($req, new Response());
        $this->assertEquals(200, $resp->getStatusCode());
        $payload = json_decode($resp->getBody());
        $this->assertEquals('phuong.huynh@go1.com', $payload->mail);
    }

    public function testCanParseHeader()
    {
        $req = Request::createFromEnvironment(Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/auth',
        ]));
        $req = $req->withHeader('authorization', 'Bearer ' . $this->jwt);
        $resp = $this->app->process($req, new Response());
        $this->assertEquals(200, $resp->getStatusCode());
        $payload = json_decode($resp->getBody());
        $this->assertEquals('phuong.huynh@go1.com', $payload->mail);
    }
}
