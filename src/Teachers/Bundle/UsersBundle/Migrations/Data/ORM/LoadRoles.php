<?php

namespace Teachers\Bundle\UsersBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;

class LoadRoles extends AbstractFixture implements DependentFixtureInterface
{
    const ROLE_TUTOR = 'ROLE_TUTOR';
    const ROLE_STUDENT = 'ROLE_STUDENT';
    const ROLE_COURSE_MANAGER = 'ROLE_COURSE_MANAGER';

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
        $tutor = new Role(self::ROLE_TUTOR);
        $tutor->setLabel('Tutor');

        $student = new Role(self::ROLE_STUDENT);
        $student->setLabel('Student');

        $courseManager = new Role(self::ROLE_COURSE_MANAGER);
        $courseManager->setLabel('Course manager');

        $manager->persist($tutor);
        $manager->persist($student);
        $manager->persist($courseManager);

        $manager->flush();
    }
}
