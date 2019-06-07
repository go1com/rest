<?php

namespace go1\rest\tests\wrapper;

use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\ServiceUrl;

class ServiceUrlTest extends RestTestCase
{
    public function test()
    {
        // examples/manifest.php > ::set('env', 'qa')
        $su = $this->container()->get(ServiceUrl::class);
        $userUrl = $su->get('user');

        $this->assertEquals('http://user.qa.go1.service', $userUrl);
    }
}
