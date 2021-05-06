<?php

namespace Teachers\Bundle\AssignmentBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

/**
 * @method AbstractEnumValue getStatus()
 * @method ExtendAssignment setStatus(AbstractEnumValue $status)
 * @method AbstractEnumValue getTerm()
 * @method ExtendAssignment setTerm(AbstractEnumValue $term)
 */
class ExtendAssignment
{
    use ExtendActivity;

    public function __construct()
    {

    }
}
