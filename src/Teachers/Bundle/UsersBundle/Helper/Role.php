<?php

namespace Teachers\Bundle\UsersBundle\Helper;

use Doctrine\ORM\EntityManager;

/**
 * Form type for TeacherGroup entity
 */
class Role
{
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

    public function getCourseManagerRoleId()
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_COURSE_MANAGER
        ])->getId();
    }

    public function getTeacherRoleId()
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_TEACHER
        ])->getId();
    }

    public function getStudentRoleId()
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_STUDENT
        ])->getId();
    }

    protected function getRoleRepository()
    {
        return $this->em->getRepository('OroUserBundle:Role');
    }
}
