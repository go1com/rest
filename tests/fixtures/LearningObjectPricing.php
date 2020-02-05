<?php

namespace go1\rest\tests\fixtures;

use go1\rest\util\Model;

/**
 * @property string $currency
 * @property float  $price
 * @property float  $tax
 * @property bool   $taxIncluded
 */
class LearningObjectPricing extends Model
{
    protected $currency;
    protected $price;
    protected $tax;
    protected $taxIncluded;
}
