<?php

namespace go1\rest\controller;

use DI\Container;
use Doctrine\DBAL\Schema\Schema;
use go1\rest\Response;
use go1\rest\wrapper\DatabaseConnection;
use go1\rest\wrapper\DatabaseConnections;

class InstallController
{
    private $container;

    /** @var DatabaseConnection */
    private $db;

    /** @var mixed DatabaseSchema */
    private $schema;

    public function __construct(Container $container)
    {
        $this->container = $container;
        list($dbClass, $schemaClass) = $container->get('restDbSchema');

        $this->db = $this->container->get($dbClass);
        $this->schema = $this->container->get($schemaClass);
    }

    public function post(Response $response)
    {
        DatabaseConnections::install(
            $this->db->get(),
            [
                function (Schema $manager) {
                    $this->schema->install($manager);
                },
            ]
        );

        return $response->withJson(null, 204);
    }
}
