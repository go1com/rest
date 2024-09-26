<?php

namespace go1\rest;

use DI\ContainerBuilder;
use DI\Definition\Source\SourceCache;
use go1\rest\controller\DefaultController;
use go1\rest\controller\health\HealthController;
use go1\rest\errors\RestErrorHandler;
use go1\rest\tests\RestTestCase;
use go1\rest\util\ResponseFactory;
use go1\rest\wrapper\CacheClient;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Headers;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use function call_user_func;
use function class_exists;
use function DI\get;
use function getenv;
use function str_replace;
use function substr;
use function sys_get_temp_dir;

class RestService extends App
{
    const VERSION     = 'v1.0';
    const SYSTEM_USER = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvYmplY3QiOnsidHlwZSI6InVzZXIiLCJjb250ZW50Ijp7ImlkIjoxLCJwcm9maWxlX2lkIjoxLCJyb2xlcyI6WyJBZG1pbiBvbiAjQWNjb3VudHMiXSwibWFpbCI6IjFAMS4xIn19fQ.YwGrlnegpd_57ek0vew5ixBfzhxiepc5ODVwPva9egs';

    private        $cnf;
    private        $name;
    private static $onBoot = null;

    public function __construct(array $cnf = [])
    {
        $this->name = ($cnf['name'] ?? getenv('REST_SERVICE_NAME')) ?: 'rest';
        $this->cnf = $cnf + $this->defaultServices();

		$containerBuilder = new ContainerBuilder();
		$containerBuilder->addDefinitions($this->cnf);
		$container = $containerBuilder->build();

        parent::__construct($container->get(ResponseFactoryInterface::class), $container);

		$this->defaultRoutes();

        if (self::$onBoot) {
            call_user_func(self::$onBoot, $this);
        }
    }

    public static function onBoot(callable $callback)
    {
        self::$onBoot = $callback;
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
                        $headers['User-Agent'] .= ' ' . substr($value, 0, 255);
                    }
                }

                return ['headers' => $headers];
            },
            ClientInterface::class     => function (Container $c) { return new Psr18Client(HttpClient::create($c->get('http-client.options'))); },
            'request'                  => function () {
				$slimRequest = ServerRequestFactory::createFromGlobals();

				// Return your custom Request object by initializing it with the Slim request data
				return new Request(
					$slimRequest->getMethod(),
					$slimRequest->getUri(),
					new Headers($slimRequest->getHeaders()),
					$slimRequest->getCookieParams(),
					$slimRequest->getServerParams(),
					$slimRequest->getBody(),
					$slimRequest->getUploadedFiles(),
				);
			},
			Response::class            => function () {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                return new Response(200, $headers);
            },
			ResponseFactoryInterface::class => function () { return new ResponseFactory(); },
            'errorHandler'             => function (Container $c) { return $c->get(RestErrorHandler::class); },
            'phpErrorHandler'          => get('errorHandler'),
            Stream::class              => function (Container $c) { return new Stream($c, $c->get('stream.transport')); },
            'stream.transport'         => null,
            LoggerInterface::class     => function () { return new NullLogger; },
            RestService::class         => $this,
            Psr16CacheInterface::class => function (Container $c) { return $c->get(CacheClient::class)->get(); },
			'notAllowedHandler'        => function ($c) {
				return function ($request, $response, $methods) use ($c) {
					$data = ['error' => 'Method Not Allowed', 'allowed_methods' => $methods];
					return $response->withStatus(405)
						->withHeader('Content-Type', 'application/json')
						->write(json_encode($data));
				};
			},
			'notFoundHandler'          => function ($c) {
				return function ($request, $response, $methods) use ($c) {
					$data = ['error' => 'Method Not Allowed', 'allowed_methods' => $methods];
					return $response->withStatus(405)
						->withHeader('Content-Type', 'application/json')
						->write(json_encode($data));
				};
			}
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

    public function serviceName(): string
    {
        return $this->name;
    }

	public function process(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		return $this->handle($request);
	}
}
