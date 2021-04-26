<?php

namespace Teachers\Bundle\BidBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

/**
 * @method AbstractEnumValue getStatus()
 * @method ExtendBid setStatus(AbstractEnumValue $status)
 */
class ExtendBid implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {

    }
}
