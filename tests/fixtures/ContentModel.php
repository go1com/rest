<?php

namespace go1\rest\tests\fixtures;

use go1\rest\util\Model;

/**
 * @property bool $published
 */
class ContentModel extends Model
{
    protected int                   $id;
    protected string                $title;
    protected LearningObjectPricing $pricing;

    /**
     * @var bool
     */
    protected $published;
}
