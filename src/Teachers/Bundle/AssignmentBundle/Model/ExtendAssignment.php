<?php

namespace Teachers\Bundle\AssignmentBundle\Model;

class ExtendAssignment
{
    const STATUS_NEW = 'new';
    const STATUS_UP_FOR_BID = 'up_for_assignment';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETE = 'complete';

    public function __construct()
    {

    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_UP_FOR_BID,
            self::STATUS_ASSIGNED,
            self::STATUS_COMPLETE
        ];
    }
}
