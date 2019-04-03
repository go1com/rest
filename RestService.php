<?php

namespace go1\rest;

use DI\ContainerBuilder;
use go1\rest\controller\ConsumeController;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class RestService extends \DI\Bridge\Slim\App
{
    const VERSION     = 'v1.0';
    const SYSTEM_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';

    private $cnf;

    /**
     * @var Stream
     */
    private $stream;

    public function __construct(array $cnf = [])
    {
        $this->cnf = $cnf + $this->defaultServices();

        parent::__construct();

        if (!empty($cnf['boot'])) {
            call_user_func($cnf['boot'], $this);
        }

        $this->defaultRoutes();
    }

    protected function defaultRoutes()
    {
        $this->get('/', function (Response $response) {
            return $response->withJson([
                'service' => defined('SERVICE_NAME') ? SERVICE_NAME : 'rest',
                'version' => defined('SERVICE_VERSION') ? SERVICE_VERSION : self::VERSION,
                'time'    => time(),
            ]);
        });

        $this->get('/consume', [new ConsumeController($this->stream()), 'get']);
        $this->post('/consume', [new ConsumeController($this->stream()), 'post']);
    }

    protected function defaultServices(): array
    {
        return [
            'http-client.options'  => function (ContainerInterface $c) {
                return [
                    'headers' => [
                        'User-Agent' => defined('SERVICE_NAME') ? SERVICE_NAME : 'rest',
                    ],
                ];
            },
            HttpClient::class      => function (ContainerInterface $c) { return HttpClient::create($c->get('http-client.options')); },
            ClientInterface::class => function () { return $this->httpClient(); },
            'request'              => function (ContainerInterface $c) { return Request::createFromEnvironment($c->get('environment')); },
            'response'             => function (ContainerInterface $c) {
                $response = new Response(200, new Headers(['Content-Type' => 'text/html; charset=UTF-8']));

                return $response->withProtocolVersion($c->get('settings')['httpVersion']);
            },
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return Response
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        return parent::process($request, $response);
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        if (empty($this->cnf)) {
            return null;
        }

        $builder->addDefinitions($this->cnf);
        $this->cnf = [];
    }

    public function stream(): Stream
    {
        if (!$this->stream) {
            $this->stream = new Stream;
        }

        return $this->stream;
    }

    public function httpClient(): ClientInterface
    {
        $client = $this->getContainer()->get(HttpClient::class);

        return new Psr18Client($client);
    }
}
