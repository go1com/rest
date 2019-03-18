<?php

namespace go1\rest;

use DI\ContainerBuilder;
use go1\rest\controller\ConsumeController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;

class RestService extends \DI\Bridge\Slim\App
{
    const VERSION = 'v1.0';

    private $cnf;

    /**
     * @var Stream
     */
    private $stream;

    public function __construct(array $cnf = [])
    {
        $this->cnf = $cnf;
        $this->cnf += [
            'request'  => function (ContainerInterface $c) {
                return Request::createFromEnvironment($c->get('environment'));
            },
            'response' => function (ContainerInterface $c) {
                $response = new Response(200, new Headers(['Content-Type' => 'text/html; charset=UTF-8']));

                return $response->withProtocolVersion($c->get('settings')['httpVersion']);
            },
        ];

        parent::__construct();

        if (!empty($cnf['boot'])) {
            call_user_func($cnf['boot'], $this);
        }

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
}
