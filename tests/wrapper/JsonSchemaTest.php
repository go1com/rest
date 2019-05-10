<?php

namespace go1\rest\tests\wrapper;

use go1\rest\tests\RestTestCase;
use go1\rest\wrapper\json_schema\JsonSchemaBuilder;
use function file_get_contents;

class JsonSchemaTest extends RestTestCase
{
    public function test()
    {
        $builder = (new JsonSchemaBuilder)
            ->linkId('https://example.com/person.schema.json')
            ->linkSchema('http://json-schema.org/draft-07/schema#')
            ->withTitle('Person');

        $builder
            ->withStringProperty('firstName')
            ->withDescription("The person's first name.");

        $builder
            ->withStringProperty('lastName')
            ->withDescription("The person's last name.");

        $builder
            ->withIntegerProperty('age')
            ->withDescription('Age in years which must be equal to or greater than zero.')
            ->withMinimum(0);

        $expect = file_get_contents(__DIR__ . '/../fixtures/json-schema-example.json');
        $actual = $builder->buildJsonString();

        $this->assertJsonStringEqualsJsonString($expect, $actual);
    }
}
