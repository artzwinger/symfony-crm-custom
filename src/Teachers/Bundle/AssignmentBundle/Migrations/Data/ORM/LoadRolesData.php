<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\UserBundle\Entity\Role;
use Teachers\Bundle\UsersBundle\Migrations\Data\ORM\LoadRoles;

/**
 * Sets permissions defined in "@TeachersAssignmentBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
 */
class LoadRolesData extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadRoles::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataPath(): string
    {
        return '@TeachersAssignmentBundle/Migrations/Data/ORM/CrmRoles/roles.yml';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRole(ObjectManager $objectManager, $roleName, $roleConfigData): ?Role
    {
        $role = parent::getRole($objectManager, $roleName, $roleConfigData);
        if (null === $role) {
            $role = new Role($roleName);
        }

        return $role;
    }
}
