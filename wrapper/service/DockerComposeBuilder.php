<?php

namespace go1\rest\wrapper\service;

use go1\rest\wrapper\Manifest;

class DockerComposeBuilder
{
    private $builder;
    private $config = [];

    public function __construct(Manifest $builder)
    {
        $this->builder = $builder;
        $this->config = [
            'version'  => '2',
            'services' => [
                'web'       => [
                    'image'       => "%IMAGE_URL%",
                    'mem_limit'   => '$MEM',
                    'ports'       => ["80:80"],
                    'environment' => [],
                ],
            ],
        ];
    }

    public function withEnv(string $name, ?string $value = null)
    {
        $this->config['services']['web']['environment'][] = is_null($value) ? "{$name}" : "{$name}={$value}";

        if ($name == 'ENV') {
            $this->config['services']['web']['environment'][] = "SERVICE_TAGS={$value}";
        }

        return $this;
    }

    public function end(): Manifest
    {
        return $this->builder;
    }

    public function build()
    {
        $service = $this->builder->service()->build();
        if (!empty($service['dbOptions'])) {
            $this->config['services']['web']['environment'][] = 'RDS_DB_HOST=$RDS_DB_HOST';
            $this->config['services']['web']['environment'][] = 'RDS_DB_SLAVE=$RDS_DB_SLAVE';
            $this->config['services']['web']['environment'][] = 'RDS_DB_USERNAME=$RDS_DB_USERNAME';
            $this->config['services']['web']['environment'][] = 'RDS_DB_PASSWORD=$RDS_DB_PASSWORD';
            $this->config['services']['web']['environment'][] = 'RDS_DB_PORT=$RDS_DB_PORT';
        }

        return $this->config;
    }
}
