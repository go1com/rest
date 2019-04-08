<?php

namespace go1\rest\examples;

require __DIR__ . '/../vendor/autoload.php';

use go1\rest\RestService;
use Slim\Http\Response;

/**
 * With Silex
 * -------
 *
 *  $rest['ctrl']       = function(Container $c) { return new MyController($c['translator']); };
 *  $rest['translator'] = function(Container $c) { return new Translator(); };
 *  $rest->get('/hello/{name}', 'ctrl:hello');
 *
 * With REST
 * -------
 *
 *  $rest->get('/hello/{name}', [MyController::class, 'hello']);
 */
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

call_user_func(
    function () {
        $rest = new RestService();
        $rest->get('/hello/{name}', [MyController::class, 'hello']);
        $rest->run();
    }
);
