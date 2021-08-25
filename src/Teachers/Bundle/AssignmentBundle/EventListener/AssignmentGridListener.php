<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentGridListener
{
    const COLUMN_NAME = 'hasUnViewedBids';
    /**
     * @var Role
     */
    private $roleHelper;

    public function __construct(Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
    }

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->roleHelper->isCurrentUserCourseManager() && !$this->roleHelper->isCurrentUserAdmin()) {
            return;
        }
        $config = $event->getConfig();
        $source = $config->getOrmQuery();
        $source->addSelect('(CASE WHEN (SUM(COALESCE(unViewedBid.viewed,0)) > 0) THEN 1 ELSE 0 END) as hasUnViewedBids');
        $source->addLeftJoin(
            'assignment.bids',
            'unViewedBid',
            'WITH',
            'unViewedBid.assignment = assignment.id and unViewedBid.viewed = 0'
        );
        $filterPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, self::COLUMN_NAME);
        $config->offsetSetByPath(
            sprintf('%s[%s]', $filterPath, FilterUtility::DATA_NAME_KEY),
            'hasUnViewedBids'
        );
        $config->offsetSetByPath(
            sprintf('%s[%s]', $filterPath, FilterUtility::TYPE_KEY),
            'boolean'
        );
        $config->offsetSetByPath(
            sprintf('%s[%s]', $filterPath, FilterUtility::BY_HAVING_KEY),
            true
        );
        $columnPath = sprintf('[%s][%s]', 'columns', self::COLUMN_NAME);
        $config->offsetSetByPath(
            sprintf('%s[%s]', $columnPath, 'label'),
            'teachers.assignment.has_unviewed_bids.label'
        );
        $config->offsetSetByPath(
            sprintf('%s[%s]', $columnPath, 'frontend_type'),
            'select'
        );
        $config->offsetSetByPath(
            sprintf('%s[%s][%s]', $columnPath, 'choices', 'teachers.assignment.has_unviewed_bids.yes.label'),
            1
        );
        $config->offsetSetByPath(
            sprintf('%s[%s][%s]', $columnPath, 'choices', 'teachers.assignment.has_unviewed_bids.no.label'),
            0
        );
    }
}
