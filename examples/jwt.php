<?php

namespace go1\rest\examples;

require __DIR__ . '/../vendor/autoload.php';

use go1\rest\RestService;
use Slim\Http\Request;
use Slim\Http\Response;

if (!function_exists('__main__')) {
    function __main__()
    {
        $app = new RestService();

        $app->get('/hello/{name}', function (Request $request, Response $res, string $name) {
            return $res->withJson([
                'name'        => [
                    'fromArgument' => $name,
                    'fromRoute'    => $request->getAttribute('routeInfo')[2]['name'],
                ],
                'jwt.payload' => $request->getAttribute('jwt.payload'),
                'attributes'  => $request->getAttributes(),
            ]);
        });

        $app->run();
    }
}

__main__();
