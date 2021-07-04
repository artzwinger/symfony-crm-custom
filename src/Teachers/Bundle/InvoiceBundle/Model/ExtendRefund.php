<?php

namespace Teachers\Bundle\InvoiceBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;

class ExtendRefund implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {

    }
}
