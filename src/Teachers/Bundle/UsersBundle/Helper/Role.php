<?php

namespace Teachers\Bundle\UsersBundle\Helper;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Oro\Bundle\UserBundle\Entity\Role as EntityRole;

/**
 * Form type for TeacherGroup entity
 */
class Role
{
    const SEARCH_FIELD_NAME = 'assigned_role';

    const ROLE_TEACHER = 'ROLE_TEACHER';
    const ROLE_STUDENT = 'ROLE_STUDENT';
    const ROLE_COURSE_MANAGER = 'ROLE_COURSE_MANAGER';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCourseManagerRoleId(): int
    {
        return $this->getCourseManagerRole()->getId();
    }

    public function getCourseManagerRole(): ?EntityRole
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_COURSE_MANAGER
        ]);
    }

    public function getTeacherRoleId(): int
    {
        return $this->getTeacherRole()->getId();
    }

    public function getTeacherRole(): ?EntityRole
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_TEACHER
        ]);
    }

    public function getStudentRoleId(): int
    {
        return $this->getStudentRole()->getId();
    }

    public function getStudentRole(): ?EntityRole
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_STUDENT
        ]);
    }

    protected function getRoleRepository(): RoleRepository
    {
        return $this->em->getRepository('OroUserBundle:Role');
    }
}
