<?php

namespace Teachers\Bundle\UsersBundle\Builder;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;
use Teachers\Bundle\UsersBundle\Helper\Role;

/**
 * Applies menu updates to the menu item
 */
class UpdateUsersManagementVisibility implements BuilderInterface
{
    /**
     * @var Role
     */
    private $roleHelper;

    /**
     * @param Role $roleHelper
     */
    public function __construct(Role $roleHelper)
    {
        $this->roleHelper = $roleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ItemInterface $menu, array $options = [], $alias = null)
    {
        if ($menu->getName() !== 'application_menu') {
            return;
        }
        foreach ($menu->getChildren() as $item) {
            if ($item->getName() === 'teachers_group') {
                if ($this->roleHelper->isCurrentUserTeacher() || $this->roleHelper->isCurrentUserStudent()) {
                    $item->setExtra('isAllowed', false);
                }
                continue;
            }
            if ($item->getName() !== 'system_tab') {
                continue;
            }
            foreach ($item->getChildren() as $child) {
                if (!in_array($child->getName(), $this->getNotAllowedSystemEntries())) {
                    continue;
                }
                if (
//                    $this->roleHelper->isCurrentUserCourseManager() ||
                    $this->roleHelper->isCurrentUserTeacher() ||
                    $this->roleHelper->isCurrentUserStudent()
                ) {
                    $child->setExtra('isAllowed', false);
                }
            }
        }
    }

    protected function getNotAllowedSystemEntries(): array
    {
        return [
            'users_management',
            'workflow_definition_list',
            'process_definition_list',
            'entities_list',
            'oro_cron_schedule',
            'oro_platform_system_info',
            'oro_message_queue_job',
            'emails',
            'localization',
            'digital_asset_list',
            'integrations_submenu',
            'tags_management',
            'audit_list',
        ];
    }
}
