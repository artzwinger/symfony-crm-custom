<?php

namespace Teachers\Bundle\AssignmentBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

/**
 * @method AbstractEnumValue getStatus()
 * @method ExtendAssignmentMessage setStatus(AbstractEnumValue $status)
 */
class ExtendAssignmentMessage implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {
    }
}
