<?php

namespace go1\rest\wrapper;

use DI\Container;
use DI\NotFoundException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Schema\Comparator;
use go1\rest\Response;

class DatabaseConnections
{
    private $container;

    const CON_OPTION_AUTO_MASTER    = 0;
    const CON_OPTION_ALWAYS_MASTER  = 1;
    const CON_OPTION_DISABLE_MASTER = 2;

    public function __construct(Container &$c)
    {
        $this->container = $c;
    }

    public function get(string $name): Connection
    {
        $key = 'dbs.' . $name;
        if (!$this->container->has($key)) {
            $config = $this->container->get("dbOptions")[$name] ?? [];
            if (empty($config)) {
                throw new NotFoundException("DB Config dbOptions[{$name}] not found.");
            }

            $this->container->set($key, DriverManager::getConnection($config));
        }

        return $this->container->get($key);
    }

    public static function connectionOptions(string $name, int $masterMode = self::CON_OPTION_AUTO_MASTER): array
    {
        if (function_exists('__db_connection_options')) {
            return __db_connection_options($name);
        }

        $prefix = strtoupper("{$name}_DB");
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $useMaster = self::CON_OPTION_ALWAYS_MASTER === $masterMode
            || ($masterMode === self::CON_OPTION_AUTO_MASTER  && 'GET' !== strtoupper($method));
        $host = self::getEnvByPriority(["{$prefix}_HOST", 'RDS_DB_HOST', 'DEV_DB_HOST']);
        if (!$useMaster) {
            $host = self::getEnvByPriority(["{$prefix}_SLAVE", 'RDS_DB_SLAVE', 'DEV_DB_SLAVE']) ?: $host;
        }

        $isDevEnv = !in_array(self::getEnvByPriority(['_DOCKER_ENV', 'ENV']), ['staging', 'production']);
        $dbName = $isDevEnv ? "{$name}_dev" : "{$name}_prod";
        if ('go1' === $name) {
            $dbName = $isDevEnv ? 'dev_go1' : 'gc_go1';
        }

        return [
            'driver'        => 'pdo_mysql',
            'dbname'        => getenv("{$prefix}_NAME") ?: $dbName,
            'host'          => $host,
            'user'          => self::getEnvByPriority(["{$prefix}_USERNAME", 'RDS_DB_USERNAME', 'DEV_DB_USERNAME']),
            'password'      => self::getEnvByPriority(["{$prefix}_PASSWORD", 'RDS_DB_PASSWORD', 'DEV_DB_PASSWORD']),
            'port'          => getenv("{$prefix}_PORT") ?: '3306',
            'driverOptions' => [1002 => 'SET NAMES utf8'],
        ];
    }

    public static function install(Connection $db, array $callbacks): Response
    {
        $db->transactional(
            function (Connection $db) use (&$callbacks) {
                $compare = new Comparator;
                $schemaManager = $db->getSchemaManager();
                $schema = $schemaManager->createSchema();
                $originSchema = clone $schema;
                $callbacks = is_array($callbacks) ? $callbacks : [$callbacks];
                foreach ($callbacks as &$callback) {
                    call_user_func($callback, $schema);
                }
                $diff = $compare->compare($originSchema, $schema);
                foreach ($diff->toSql($db->getDatabasePlatform()) as $sql) {
                    try {
                        $db->executeQuery($sql);
                    } catch (TableExistsException $e) {
                    }
                }
            }
        );

        return (new Response())->jr([], 204);
    }

    private static function getEnvByPriority(array $names)
    {
        foreach ($names as $name) {
            if ($value = getenv($name)) {
                return $value;
            }
        }

        return null;
    }
}
