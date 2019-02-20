<?php

namespace go1\rest;

use DI\ContainerBuilder;
use Firebase\JWT\JWT;
use Slim\Http\Request;
use Slim\Http\Response;

class RestService extends \DI\Bridge\Slim\App
{
    const VERSION = 'v1.0';

    private $cnf;

    public function __construct(array $cnf = [])
    {
        $this->cnf = $cnf;
        
        # $this->cnf[] = '';

        parent::__construct();

        $this
            ->add($this->jwtMiddleware())
            ->get('/', function (Response $response) {
                return $response->withJson([
                    'service' => defined('SERVICE_NAME') ? SERVICE_NAME : 'rest',
                    'version' => defined('SERVICE_VERSION') ? SERVICE_VERSION : self::VERSION,
                    'time'    => time(),
                ]);
            });
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        if (empty($this->cnf)) {
            return null;
        }

        $builder->addDefinitions($this->cnf);
        $this->cnf = [];
    }

    /**
     * Middleware to convert JWT from query|header|cookie into attribute jwt.payload
     *
     * Note: This is not JWT validation
     *
     * @return callable
     */
    private function jwtMiddleware()
    {
        return function (Request $request, Response $response, callable $next) {
            $auth = $request->getHeader('Authorization');
            if ($auth && (0 === strpos('Bearer ', $auth))) {
                $jwt = substr($auth, 7);
            }

            $jwt = $jwt ?? $request->getQueryParam('jwt') ?? $request->getCookieParam('jwt');
            $jwt = is_null($jwt) ? null : (2 !== substr_count($jwt, '.')) ? null : explode('.', $jwt)[1];
            $jwt = is_null($jwt) ? null : JWT::jsonDecode(JWT::urlsafeB64Decode($jwt));

            return $next(
                is_null($jwt) ? $request : $request->withAttribute('jwt.payload', $jwt),
                $response
            );
        };
    }
}
