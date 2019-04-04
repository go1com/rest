<?php

namespace go1\rest;

use DI\ContainerBuilder;
use Exception;
use go1\rest\controller\ConsumeController;
use go1\rest\errors\RestError;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Http\Headers;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use function defined;

class RestService extends \DI\Bridge\Slim\App
{
    const VERSION     = 'v1.0';
    const SYSTEM_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';

    private $cnf;

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

        $this->get('/consume', [ConsumeController::class, 'get']);
        $this->post('/consume', [ConsumeController::class, 'post']);
    }

    protected function defaultServices(): array
    {
        return [
            'http-client.options'  => function () {
                $headers['User-Agent'] = defined('SERVICE_NAME') ? SERVICE_NAME : 'rest';

                return ['headers' => $headers];
            },
            ClientInterface::class => function (Container $c) { return new Psr18Client(HttpClient::create($c->get('http-client.options'))); },
            'request'              => function (Container $c) { return Request::createFromEnvironment($c->get('environment')); },
            'response'             => function (Container $c) {
                $res = new Response(200, new Headers(['Content-Type' => 'text/html; charset=UTF-8']));

                return $res->withProtocolVersion($c->get('settings')['httpVersion']);
            },
            'errorHandler'         => function () { return [$this, 'error']; },
            Stream::class          => function (Container $c) { return new Stream($c->get('stream.transport')); },
            'stream.transport'     => null,
            LoggerInterface::class => function () { return new NullLogger; },
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

    protected function error(Request $request, Response $response, Exception $e)
    {
        if ($e instanceof RestError) {
            return $response->withJson(
                ['code' => $e->errorCode(), 'message' => $e->getMessage()],
                $e->httpErrorCode()
            );
        }

        throw $e;
    }
}
