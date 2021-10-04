<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Teachers\Bundle\UsersBundle\Helper\Role;

class HideAppoveActionsForStudentsTutors
{
    /**
     * @var Role
     */
    private $roleHelper;

    public function __construct(Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
    }

    /**
     * oro_datagrid.datagrid.build.before.teachers-assignment-messages-grid
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        if (!$this->roleHelper->isCurrentUserStudent() && !$this->roleHelper->isCurrentUserTeacher()) {
            return;
        }
        $config = $event->getConfig();
        $approveActionPath = '[actions][approve]';
        $unApproveActionPath = '[actions][unapprove]';
        $config->offsetUnsetByPath($approveActionPath);
        $config->offsetUnsetByPath($unApproveActionPath);
    }
}
