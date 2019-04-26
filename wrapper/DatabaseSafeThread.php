<?php

namespace go1\rest\wrapper;

use Doctrine\DBAL\Connection;

class DatabaseSafeThread
{
    public static function run(Connection $db, string $threadName, int $timeout, callable $callback)
    {
        if ('sqlite' === $db->getDatabasePlatform()->getName()) {
            // TODO: define simple table __lock__
            //   thread_name: unique
            //   tll: time out
            // I transaction awareness process
            //  1. create lock with ttls
            //  2. process
            //  3. release lock

            return $callback($db);
        }

        try {
            $db->executeQuery('DO GET_LOCK("' . $threadName . '", ' . $timeout . ')');

            return $callback($db);
        } finally {
            $db->executeQuery('DO RELEASE_LOCK("' . $threadName . '")');
        }
    }
}
