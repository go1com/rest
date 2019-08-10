<?php
namespace go1\rest\tests\fixtures\Marshaller;

use go1\rest\util\Model;

/**
 * @property string $stripeIdDescription
 * @property int    $stripeId
 */
class PropertiesWithTheSamePrefixModel extends Model
{
    /**
     * @json stripe_id
     */
    protected $stripeId;

    /**
     * @json stripe_id_description
     */
    protected $stripeIdDescription;
}
