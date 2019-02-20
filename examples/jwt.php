<?php

namespace go1\rest\examples;

require __DIR__ . '/../vendor/autoload.php';

use go1\rest\RestService;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * For restful microservices, access JWT payload is frequently use case; so we parse JWT into jwt.payload by default.
 *
 * # Start HTTP server
 * # ---------------------
 * cd rest/examples
 * php -S localhost:8989
 *
 * # Run example
 * # ---------------------
 * JWT=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c
 * curl localhost:8989/hello/there?jwt=$JWT
 */

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
