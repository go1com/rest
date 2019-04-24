<?php

namespace go1\rest\examples;

use Doctrine\DBAL\Connection;
use go1\rest\Response;
use go1\rest\wrapper\response\TextGeneratorStream;
use PDO;

/**
 * Motivation
 * =======
 *
 * Stream large data from database to client.
 *
 * # Route definition
 * $rest
 *      ->get('/stream', [ExampleController::class, 'get'])
 *      ->setOutputBuffering(false);
 *
 * Ref:
 *
 * - https://discourse.slimframework.com/t/how-to-stream-a-big-file-through-slim/175
 * - https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/#x-accel-buffering
 * - http://ndjson.org/
 */
class ExampleController
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;

        $this->db
            ->getWrappedConnection()
            ->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    public function get(Response $response)
    {
        return $response
            ->withBody($this->streamBody())
            ->withHeader('X-Accel-Buffering', 'no')
            ->withHeader('Content-Type', 'application/x-ndjson');
    }

    private function streamBody()
    {
        return new TextGeneratorStream($this->streamGenerator());
    }

    private function streamGenerator()
    {
        $q = 'SELECT * FROM large_table ORDER BY id';
        $q = $this->db->executeQuery($q);
        while ($row = $q->fetch(PDO::FETCH_OBJ)) {
            yield json_encode($row);
        }
    }
}
