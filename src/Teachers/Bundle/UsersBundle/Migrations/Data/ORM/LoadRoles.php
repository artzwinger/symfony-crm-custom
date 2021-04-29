<?php

namespace Teachers\Bundle\UsersBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Teachers\Bundle\UsersBundle\Helper\Role as RoleHelper;

class LoadRoles extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return ['Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $tutor = new Role(RoleHelper::ROLE_TEACHER);
        $tutor->setLabel('Teacher');

        $student = new Role(RoleHelper::ROLE_STUDENT);
        $student->setLabel('Student');

        $courseManager = new Role(RoleHelper::ROLE_COURSE_MANAGER);
        $courseManager->setLabel('Course Manager');

        $manager->persist($tutor);
        $manager->persist($student);
        $manager->persist($courseManager);

        $manager->flush();
    }
}
