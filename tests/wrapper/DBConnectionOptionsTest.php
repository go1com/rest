<?php

namespace go1\rest\tests\wrapper;

use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\DatabaseConnections;

class DBConnectionOptionsTest extends RestTestCase
{
    /**
     * @dataProvider getData
     */
    public function testGet(
        array $environment,
        string $method,
        string $name,
        int $masterMode,
        array $expected
    )
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $this->putEnvironment($environment);
        $result = DatabaseConnections::connectionOptions($name, $masterMode);
        $this->assertSubset($expected, $result);
    }

    protected function putEnvironment(array $environment)
    {
        foreach ($environment as $k => $v) {
            putenv("{$k}={$v}");
        }
    }

    protected function assertSubset(array $expected, array $result)
    {
        foreach ($expected as $k => $v) {
            $this->assertArrayHasKey($k, $result);
            if (is_array($v)) {
                $this->assertSubset($v, $result[$k] ?? []);
            } else {
                $this->assertEquals($v, $result[$k]);
            }
        }
    }

    public function getData(): array
    {
        $baseEnv = [
            'EVENT_DB_NAME'   => 'event_dev',
            'EVENT_DB_SLAVE'  => 'slave.rds.go1.co',
            'EVENT_DB_HOST'   => 'master.rds.go1.co',
            'RDS_DB_USERNAME' => 'user_event',
            'RDS_DB_PASSWORD' => 'user_event_pass',
        ];

        return [
            [
                [],     // environment
                'GET',
                'go1',  // name
                DatabaseConnections::CON_OPTION_AUTO_MASTER,
                [       // expected
                        'driver'        => 'pdo_mysql',
                        'dbname'        => 'dev_go1',
                        'driverOptions' => [1002 => 'SET NAMES utf8'],
                ],
            ],
            [
                $baseEnv,
                'GET',
                'event',
                DatabaseConnections::CON_OPTION_AUTO_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'slave.rds.go1.co',
                    'user'     => 'user_event',
                    'password' => 'user_event_pass',
                ],
            ],

            [
                $baseEnv,
                'POST',
                'event',
                DatabaseConnections::CON_OPTION_AUTO_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'master.rds.go1.co',
                    'user'     => 'user_event',
                    'password' => 'user_event_pass',
                ],
            ],

            [
                $baseEnv,
                'GET',
                'event',
                DatabaseConnections::CON_OPTION_ALWAYS_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'master.rds.go1.co',
                    'user'     => 'user_event',
                    'password' => 'user_event_pass',
                ],
            ],

            [
                $baseEnv,
                'POST',
                'event',
                DatabaseConnections::CON_OPTION_DISABLE_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'slave.rds.go1.co',
                    'user'     => 'user_event',
                    'password' => 'user_event_pass',
                ],
            ],

            // specify user
            [
                $baseEnv + ['EVENT_DB_USERNAME' => 'go1'],
                'GET',
                'event',
                DatabaseConnections::CON_OPTION_AUTO_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'slave.rds.go1.co',
                    'user'     => 'go1',
                    'password' => 'user_event_pass',
                ],
            ],
            // specify user
            [
                $baseEnv + ['EVENT_DB_USERNAME_SLAVE' => 'slave_user', 'EVENT_DB_PASSWORD_SLAVE' => 'slave_pass'],
                'GET',
                'event',
                DatabaseConnections::CON_OPTION_DISABLE_MASTER,
                [
                    'dbname'   => 'event_dev',
                    'host'     => 'slave.rds.go1.co',
                    'user'     => 'slave_user',
                    'password' => 'slave_pass',
                ],
            ]
        ];
    }
}
