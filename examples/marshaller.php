<?php

namespace go1\rest\examples;

use go1\rest\util\Marshaller;
use go1\rest\util\Model;

/**
 * @property int    $id
 * @property string $mail
 * @property string $firstName
 * @property string $lastName
 * @property int    $age
 */
class UserExample extends Model
{

    protected $id;

    # Map column name in database and model in case they're difference
    /**
     * @db email
     */
    protected $mail;

    # @TRUONG will update later
    /**
     * @json first_name
     */
    protected $firstName;

    protected $lastName;

    # If value is null, this field will be ingored on dump (inspired by go structure definition)
    /**
     * @omitEmpty
     */
    protected $age;
}

call_user_func(function () {
    $marshaller = (new Marshaller);
    $input = (object) [
        'id'         => 1,
        'email'      => 'alice@qa.co',
        'first_name' => 'alice',
        'last_name'  => 'bar',
        'age'        => null,
    ];

    # Parse raw object to model
    $obj = $marshaller->parse($input, $obj, ['db']);

    # Dump model to raw object
    $dump = $marshaller->dump($obj);
});
