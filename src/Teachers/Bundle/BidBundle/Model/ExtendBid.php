<?php

namespace Teachers\Bundle\BidBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Teachers\Bundle\BidBundle\Entity\Bid;

/**
 * @method AbstractEnumValue getStatus
 * @method Bid setStatus(AbstractEnumValue $value)
 */
class ExtendBid implements ActivityInterface
{
    use ExtendActivity;

    const STATUS_OPEN = 'open';
    const STATUS_WINNING = 'winning';

    public function __construct()
    {

    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_WINNING
        ];
    }
}
