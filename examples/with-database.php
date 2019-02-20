<?php

namespace go1\rest\examples;

use go1\rest\RestService;
use go1\rest\wrapper\DatabaseConnections;
use go1\util\DB;
use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

/**
 * With Silex
 * -------
 *
 *  $app['ctrl'] = function(Container $c) { new PortalSingleController($c['dbs']['go1'], $c['dbs']['staff']); };
 *  $app->get('/portal/{id}', 'ctrl:get');
 *
 * With REST
 * -------
 *
 *  $app->get('/portal/{id}', [PortalSingleController::class, 'get']);
 */

if (!function_exists('__main__')) {
    function __main__()
    {
        $app = new RestService([
            'dbOptions' => [
                'go1'   => DB::connectionOptions('go1'),
                'staff' => DB::connectionOptions('go1'),
            ],
        ]);

        $app->get('/portal/{id}', [PortalSingleController::class, 'get']);
        $app->run();
    }
}

class PortalSingleController
{
    private $go1;
    private $staff;

    public function __construct(DatabaseConnections $connections)
    {
        $this->go1 = $connections->get('go1');
        $this->staff = $connections->get('staff');
    }

    public function get(int $id, Response $res)
    {
        return $res->withJson(['wip' => true, 'id' => $id]);
    }
}

__main__();
