<?php

namespace go1\rests\tests;

use go1\rest\tests\fixtures\LearningObject;
use go1\rest\tests\RestTestCase;
use go1\rest\util\Marshaller;
use go1\rest\util\Model;
use go1\rest\tests\fixtures\Marshaller\PropertiesWithTheSamePrefixModel;

class MarshallerTest extends RestTestCase
{
    public function testPropertyComment()
    {
        $obj = new class extends Model
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
        $obj = $this->get(Marshaller::class)->parse($input, $obj, ['db']);
        $this->assertEquals(123, $obj->portalId);
        $this->assertEquals(345, $obj->userId);

        $input = (object) ['portalId' => '456', 'userId' => 789];
        $obj = $this->get(Marshaller::class)->parse($input, $obj, ['json']);
        $this->assertEquals(456, $obj->portalId);
        $this->assertEquals(789, $obj->userId);
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
        $obj = $this->get(Marshaller::class)->parse($input, new LearningObject, ['json', 'db']);

        $this->assertEquals(111, $obj->id);
        $this->assertEquals(222, $obj->portalId);
        $this->assertEquals('AUD', $obj->pricing->currency);
        $this->assertEquals(10.00, $obj->pricing->price);
        $this->assertEquals(true, $obj->metadata->singleLi);
    }

    public function testDumpWithOmmitEmpty()
    {
        $obj = new class extends Model
        {
            /**
             * @var int
             * @json userId
             * @omitEmpty
             */
            public $userId;

            /**
             * @var int
             * @json id
             */
            public $id;
        };

        $marshaller = $this->get(Marshaller::class);
        $input = (object) ['id' => null, 'userId' => null];
        $obj = $marshaller->parse($input, $obj, ['db']);

        $dump = $this->get(Marshaller::class)->dump($obj);
        $this->assertEquals(['id' => null], $dump);
    }

    public function testSomePropertiesWithTheSamePrefix()
    {
        $obj   = new PropertiesWithTheSamePrefixModel();
        $input = (object)[
            'stripe_id'          => '1',
            'stripe_id_description' => 'some comments',
        ];
        $marshaller = $this->get(Marshaller::class);
        $obj = $marshaller->parse($input, $obj, ['json']);

        $this->assertEquals(gettype($obj->stripeId), gettype(1));
        $this->assertEquals(gettype($obj->stripeIdDescription), gettype('hello'));
    }
}
