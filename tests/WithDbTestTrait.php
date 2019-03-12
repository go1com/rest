<?php

namespace go1\rest\tests;

use go1\rest\RestService;

trait WithDbTestTrait
{
    protected function mockDbs(RestService $rest)
    {
        $settings = $rest->getContainer()->get('dbOptions');
        $options = [];
        foreach (array_keys($settings) as $name) {
            $options[$name] = ['url' => 'sqlite://sqlite::memory:'];
        }
        $rest->getContainer()->set('dbOptions', $options);
    }
}
