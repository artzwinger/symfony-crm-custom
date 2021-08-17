<?php

namespace Teachers\Bundle\ApplicationBundle\Model;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

/**
 * @method AbstractEnumValue getStatus
 * @method Application setStatus(AbstractEnumValue $value)
 * @method AbstractEnumValue getTerm()
 * @method Application setTerm(AbstractEnumValue $term)
 * @method AbstractEnumValue getRep()
 * @method Application setRep(AbstractEnumValue $term)
 */
class ExtendApplication
{
    public function __construct()
    {

    }
}
