<?php

namespace go1\rest\examples;

use go1\rest\RestService;
use go1\rest\wrapper\DatabaseConnections;
use go1\rest\wrapper\ElasticSearchClients;
use go1\util\DB;
use Slim\Http\Response;

require __DIR__ . '/../vendor/autoload.php';

/**
 * With Silex
 * -------
 *
 *  # define service
 *  $app['ctrl'] = function(Container $c) {
 *      return new PortalSingleController(
 *          $c['dbs']['go1'], $c['dbs']['staff'],
 *          $c['go1.client.es']);
 *      };
 *
 *  # define routing
 *  $app->get('/portal/{id}', 'ctrl:get');
 *
 * With REST
 * -------
 *
 *  $app->get('/portal/{id}', [PortalSingleController::class, 'get']);
 */
class PortalSingleController
{
    private $go1;
    private $staff;
    private $es;

    public function __construct(DatabaseConnections $connections, ElasticSearchClients $esClients)
    {
        $this->go1 = $connections->get('go1');
        $this->staff = $connections->get('staff');
        $this->es = $esClients->default();
    }

    public function get(int $id, Response $res)
    {
        return $res->withJson(['wip' => true, 'id' => $id]);
    }
}

call_user_func(
    function () {
        $app = new RestService([
            'dbOptions' => [
                'go1'   => DB::connectionOptions('go1'),
                'staff' => DB::connectionOptions('go1'),
            ],
        ]);

        $app->get('/portal/{id}', [PortalSingleController::class, 'get']);
        $app->run();
    }
);
