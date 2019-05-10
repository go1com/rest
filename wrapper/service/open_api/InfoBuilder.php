<?php

namespace go1\rest\wrapper\service\open_api;

use function array_filter;

class InfoBuilder
{
    private $api;
    private $config;

    public function __construct(OpenApiBuilder $api, &$config)
    {
        $this->api = $api;
        $this->config = &$config;
    }

    public function withTitle(string $title)
    {
        $this->config['title'] = $title;

        return $this;
    }

    public function withDescription(string $description)
    {
        $this->config['description'] = $description;

        return $this;
    }

    public function withVersion(string $version)
    {
        $this->config['version'] = $version;

        return $this;
    }

    public function withTermOfService(string $tos)
    {
        $this->config['termsOfService'] = $tos;

        return $this;
    }

    public function withLicense(string $name, string $url = '')
    {
        $this->config['license'] = array_filter([
            'name' => $name,
            'url'  => $url,
        ]);

        return $this;
    }

    public function withContact(string $name, string $url = '', string $email = '')
    {
        $this->config['contact'] = array_filter([
            'name'  => $name,
            'url'   => $url,
            'email' => $email,
        ]);

        return $this;
    }

    public function endInfo()
    {
        return $this->api;
    }
}
