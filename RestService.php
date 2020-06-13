<?php

namespace go1\rest;

use DI\ContainerBuilder;
use DI\Definition\Source\SourceCache;
use go1\rest\controller\DefaultController;
use go1\rest\controller\health\HealthController;
use go1\rest\errors\RestErrorHandler;
use go1\rest\tests\RestTestCase;
use go1\rest\util\MessageFactory;
use go1\rest\wrapper\CacheClient;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Slim\Http\Headers;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use DDTrace\Bootstrap;
use DDTrace\Integrations\IntegrationsLoader;
use function class_exists;
use function DI\get;
use function getenv;
use function str_replace;
use function substr;
use function sys_get_temp_dir;

/**
 * @method Response process(ServerRequestInterface $request, ResponseInterface $response)
 */
class RestService extends \DI\Bridge\Slim\App
{
    const VERSION     = 'v1.0';
    const SYSTEM_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';

    private $cnf;
    private $name;

    public function __construct(array $cnf = [])
    {
        $this->name = ($cnf['name'] ?? getenv('REST_SERVICE_NAME')) ?: 'rest';
        $this->cnf = $cnf + $this->defaultServices();

        parent::__construct();
        $this->registerTracingService();
        $this->defaultRoutes();

        if (!empty($cnf['boot'])) {
            call_user_func($cnf['boot'], $this);
        }
    }

    private function registerTracingService()
    {
        if (!empty(getenv('DD_AGENT_HOST')) && extension_loaded('ddtrace')) {
            Bootstrap::tracerOnce();
            IntegrationsLoader::load();
        }
    }

    protected function defaultRoutes()
    {
        $this->get('/', [DefaultController::class, 'get']);
        $this->get('/healthz', [HealthController::class, 'get']);
    }

    protected function defaultServices(): array
    {
        return [
            'http-client.options'      => function () {
                $headers['User-Agent'] = getenv('REST_SERVICE_NAME') ?: 'rest';

                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 7) == 'HTTP_X_') {
                        // Add header to request, follow by section Fielding of RFC 2616
                        // Example from `$_SERVER['HTTP_X_REQUEST_ID']` we will have the header name `X-Request-Id`
                        // @see: http://php.net/manual/en/function.getallheaders.php#84262
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    } elseif ($name === 'User-Agent') {
                        $headers['User-Agent'] .= ' ' .substr($value, 0, 255);
                    }
                }

                return ['headers' => $headers];
            },
            ClientInterface::class     => function (Container $c) { return new Psr18Client(HttpClient::create($c->get('http-client.options'))); },
            'request'                  => function (Container $c) { return Request::createFromEnvironment($c->get('environment')); },
            'response'                 => function (Container $c) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                $res = new Response(200, $headers);
                $ver = $c->get('settings')['httpVersion'];

                return $res->withProtocolVersion($ver);
            },
            'errorHandler'             => function (Container $c) { return $c->get(RestErrorHandler::class); },
            'phpErrorHandler'          => get('errorHandler'),
            Stream::class              => function (Container $c) { return new Stream($c, $c->get('stream.transport')); },
            'stream.transport'         => null,
            LoggerInterface::class     => function () { return new NullLogger; },
            RestService::class         => $this,
            Psr16CacheInterface::class => function (Container $c) { return $c->get(CacheClient::class)->get(); },
        ];
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        if (empty($this->cnf)) {
            return null;
        }

        $testing = class_exists(RestTestCase::class, false);
        if (!$testing) {
            if (!empty($this->cnf['di.enable-compile'])) {
                $builder->enableCompilation(
                    sys_get_temp_dir(),
                    'CompiledContainer__' . str_replace('-', '__', $this->serviceName())
                );
                unset($this->cnf['di.enable-compile']);
            }
        }

        $builder->addDefinitions($this->cnf);

        if (!$testing && SourceCache::isSupported()) {
            $builder->enableDefinitionCache();
        }

        $this->cnf = [];
    }

    public function handle(Request $req)
    {
        return $this->process($req, (new MessageFactory)->createResponse());
    }

    public function serviceName(): string
    {
        return $this->name;
    }
}
