<?php

namespace go1\rest\examples;

require __DIR__ . '/../vendor/autoload.php';

use go1\rest\RestService;
use Slim\Http\Response;

/**
 * With Silex
 * -------
 *
 *  $app['ctrl']       = function(Container $c) { return new MyController($c['translator']); };
 *  $app['translator'] = function(Container $c) { return new Translator(); };
 *  $app->get('/hello/{name}', 'ctrl:hello');
 *
 * With REST
 * -------
 *
 *  $app->get('/hello/{name}', [MyController::class, 'hello']);
 */

if (!function_exists('__main__')) {
    function __main__()
    {
        $app = new RestService();
        $app->get('/hello/{name}', [MyController::class, 'hello']);
        $app->run();
    }
}

class Translator
{
    public function translate(string $input)
    {
        return strtoupper($input);
    }
}

class MyController
{
    private $t;

    public function __construct(Translator $t)
    {
        $this->t = $t;
    }

    public function hello(Response $res, string $name)
    {
        return $res->withJson(['hello' => $this->t->translate($name)]);
    }
}

__main__();

