<?php

namespace Teachers\Bundle\InvoiceBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

/**
 * @method AbstractEnumValue getStatus()
 * @method ExtendInvoice setStatus(AbstractEnumValue $status)
 */
class ExtendInvoice implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {

    }
}
