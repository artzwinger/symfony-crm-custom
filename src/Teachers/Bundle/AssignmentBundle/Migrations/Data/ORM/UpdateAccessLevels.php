<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\UsersBundle\Helper\Role;

class UpdateAccessLevels extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [LoadRolesData::class];
    }

    /**
     * Load ACL for security roles
     *
     * @param ObjectManager $manager
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }
        $this->setEntityPermissions(
            $aclManager,
            $this->getRole($manager, Role::ROLE_TEACHER),
            Assignment::class,
            ['VIEW_TEACHERS_QUEUE_SYSTEM', 'VIEW_MY_COURSES_SYSTEM']
        );
        $this->setEntityPermissions(
            $aclManager,
            $this->getRole($manager, Role::ROLE_STUDENT),
            Assignment::class,
            ['VIEW_MY_COURSES_SYSTEM']
        );
        $aclManager->flush();
    }
}
