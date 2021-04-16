<?php

namespace Teachers\Bundle\BidBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;

class ExtendBid implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {

    }
}
