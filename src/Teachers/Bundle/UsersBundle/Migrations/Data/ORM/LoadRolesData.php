<?php

namespace Teachers\Bundle\UsersBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * Sets permissions defined in "@TeachersUsersBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
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
        return '@TeachersUsersBundle/Migrations/Data/ORM/CrmRoles/roles.yml';
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
