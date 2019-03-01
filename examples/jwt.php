<?php

namespace go1\rest\examples;

require __DIR__ . '/../vendor/autoload.php';

use go1\rest\Request;
use go1\rest\Response;
use go1\rest\RestService;

/**
 * For restful microservices, access JWT payload is frequently use case; so we parse JWT into jwt.payload by default.
 *
 * JWt parsing:
 *  - in go1/app: Run as middleware, it's always parsed.
 *  - in go1/rest: Only parse JWT payload if we really needed it.
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
call_user_func(
    function () {
        $app = new RestService();

        $app->get('/hello/{name}', function (Request $request, Response $res) {
            return $res->withJson([
                'jwt' => [
                    'contextUser'            => $request->contextUser(),
                    'isSystemUser'           => $request->isSystemUser(),
                    'isPortalAdmin'          => $request->isPortalAdmin('qa.mygo1.com'),
                    'isPortalManager'        => $request->isPortalManager('qa.mygo1.com'),
                    'isPortalContentManager' => $request->isPortalContentAdministrator('qa.mygo1.com'),
                ],
            ]);
        });

        $app->run();
    }
);
