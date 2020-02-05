<?php

namespace go1\rest\tests\fixtures;

use go1\rest\util\Model;

/**
 * @property int                                             $id
 * @property string                                          $type
 * @property bool                                            $published
 * @property bool                                            $private
 * @property bool                                            $marketplace
 * @property int                                             $portalId
 * @property \go1\rest\tests\fixtures\LearningObjectPricing  $pricing
 * @property \go1\rest\tests\fixtures\LearningObjectMetadata $metadata
 */
class LearningObject extends Model
{
    protected $id;
    protected $type;
    protected $published;
    protected $private;
    protected $marketplace;

    /**
     * @db   instance_id
     * @json portalId
     */
    protected $portalId;
    protected $pricing;

    /**
     * @json data
     */
    protected $metadata;
}

/**
 * @property bool $singleLi
 */
class LearningObjectMetadata extends Model
{
    /**
     * @json single_li
     */
    protected $singleLi = false;
}
