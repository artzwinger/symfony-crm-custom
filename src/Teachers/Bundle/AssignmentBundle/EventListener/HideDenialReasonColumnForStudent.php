<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Teachers\Bundle\UsersBundle\Helper\Role;

class HideDenialReasonColumnForStudent
{
    const DENIAL_REASON_COLUMN_NAME = 'denialReason';
    /**
     * @var Role
     */
    private $roleHelper;

    public function __construct(Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
    }

    /**
     * oro_datagrid.datagrid.build.before.teachers-assignment-my-messages-grid
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->roleHelper->isCurrentUserStudent()) {
            return;
        }
        $config = $event->getConfig();
        $filterPath = sprintf('%s[%s]', FilterConfiguration::COLUMNS_PATH, self::DENIAL_REASON_COLUMN_NAME);
        $columnPath = sprintf('[%s][%s]', 'columns', self::DENIAL_REASON_COLUMN_NAME);
        $sortPath = sprintf('[%s][%s][%s]', 'sorters', 'columns', self::DENIAL_REASON_COLUMN_NAME);
        $config->offsetUnsetByPath($filterPath);
        $config->offsetUnsetByPath($columnPath);
        $config->offsetUnsetByPath($sortPath);
    }
}
