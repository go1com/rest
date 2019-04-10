<?php

namespace go1\rest;

use DI\ContainerBuilder;
use Exception;
use go1\rest\controller\DefaultController;
use go1\rest\errors\RestError;
use go1\rest\tests\RestTestCase;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Http\Headers;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use function class_exists;
use function getenv;

/**
 * @method Response process(ServerRequestInterface $request, ResponseInterface $response)
 */
class RestService extends \DI\Bridge\Slim\App
{
    const VERSION     = 'v1.0';
    const SYSTEM_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';

    private $cnf;

    public function __construct(array $cnf = [])
    {
        $this->cnf = $cnf + $this->defaultServices();

        parent::__construct();

        $this->defaultRoutes();

        if (!empty($cnf['boot'])) {
            call_user_func($cnf['boot'], $this);
        }
    }

    protected function defaultRoutes()
    {
        $this->get('/', [DefaultController::class, 'get']);
    }

    protected function defaultServices(): array
    {
        $rest = $this;

        return [
            'http-client.options'  => function () {
                $headers['User-Agent'] = getenv('REST_SERVICE_NAME') ?: 'rest';

                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 7) == 'HTTP_X_') {
                        // Add header to request, follow by section Fielding of RFC 2616
                        // Example from `$_SERVER['HTTP_X_REQUEST_ID']` we will have the header name `X-Request-Id`
                        // @see: http://php.net/manual/en/function.getallheaders.php#84262
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }

                return ['headers' => $headers];
            },
            ClientInterface::class => function (Container $c) { return new Psr18Client(HttpClient::create($c->get('http-client.options'))); },
            'request'              => function (Container $c) { return Request::createFromEnvironment($c->get('environment')); },
            'response'             => function (Container $c) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                $res = new Response(200, $headers);
                $ver = $c->get('settings')['httpVersion'];

                return $res->withProtocolVersion($ver);
            },
            'errorHandler'         => function () { return [$this, 'error']; },
            Stream::class          => function (Container $c) { return new Stream($c, $c->get('stream.transport')); },
            'stream.transport'     => null,
            LoggerInterface::class => function () { return new NullLogger; },
            RestService::class     => function () use ($rest) { return $rest; },
        ];
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
                [
                    'code'    => $e->errorCode(),
                    'message' => $e->getMessage(),
                    'trace'   => !class_exists(RestTestCase::class, false) ? '' : $e->getTraceAsString(),
                ],
                $e->httpErrorCode()
            );
        }

        throw $e;
    }
}
