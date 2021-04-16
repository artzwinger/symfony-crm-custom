<?php

namespace Teachers\Bundle\AssignmentBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\ActivityBundle\Model\ExtendActivity;

class ExtendAssignmentPrivateNote implements ActivityInterface
{
    use ExtendActivity;

    public function __construct()
    {
    }
}
