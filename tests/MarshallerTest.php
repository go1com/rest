<?php

namespace go1\rests\tests;

use go1\core\lo\model\LearningObject;
use go1\rest\tests\RestTestCase;
use go1\rest\util\Marshaller;

class MarshallerTest extends RestTestCase
{
    public function testPropertyComment()
    {
        $obj = new class
        {
            /**
             * @var int
             * @json portalId
             * @db   instance_id
             */
            public $portalId;

            /**
             * @var int
             * @json userId
             */
            public $userId;
        };

        $input = (object) ['instance_id' => '123', 'userId' => 345];
        $obj = (new Marshaller)->parse($input, $obj, ['db']);
        $this->assertEquals(123, $obj->portalId);
        $this->assertEquals(345, $obj->userId);

        $input = (object) ['portalId' => '123', 'userId' => 345];
        $obj = (new Marshaller)->parse($input, $obj, ['json']);
        $this->assertEquals(123, $obj->portalId);
    }

    public function testLearningObject()
    {
        $input = (object) [
            'id'          => 111,
            'instance_id' => 222,
            'pricing'     => (object) ['currency' => 'AUD', 'price' => 10.00],
            'data'        => (object) ['single_li' => true,],
        ];

        /** @var LearningObject $obj */
        $obj = (new Marshaller)->parse($input, new LearningObject, ['json', 'db']);

        $this->assertEquals(111, $obj->id);
        $this->assertEquals(222, $obj->portalId);
        $this->assertEquals('AUD', $obj->pricing->currency);
        $this->assertEquals(10.00, $obj->pricing->price);
        $this->assertEquals(true, $obj->metadata->singleLi);
    }
}
