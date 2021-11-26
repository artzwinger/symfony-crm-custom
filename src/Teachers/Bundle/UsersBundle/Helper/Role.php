<?php

namespace Teachers\Bundle\UsersBundle\Helper;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;
use Oro\Bundle\UserBundle\Entity\Role as RoleEntity;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Form type for TeacherGroup entity
 */
class Role
{
    const SEARCH_FIELD_NAME = 'assigned_role';

    const ROLE_TEACHER = 'ROLE_TEACHER';
    const ROLE_STUDENT = 'ROLE_STUDENT';
    const ROLE_COURSE_MANAGER = 'ROLE_COURSE_MANAGER';
    const ROLE_ADMINISTRATOR = 'ROLE_ADMINISTRATOR';

    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getUsersByRole(RoleEntity $role)
    {
        return $this->getRoleRepository()->getUserQueryBuilder($role)->getQuery()->getResult();
    }

    public function getAdmins()
    {
        return $this->getUsersByRole($this->getAdminRole());
    }

    public function getCourseManagers()
    {
        return $this->getUsersByRole($this->getCourseManagerRole());
    }

    public function hasUserOneOfRoleNames(User $user, array $roleNames): bool
    {
        foreach ($user->getRoles() as $role) {
            /** @var RoleEntity $role */
            if (in_array($role->getRole(), $roleNames)) {
                return true;
            }
        }
        return false;
    }

    public function isCurrentUserCourseManager(): bool
    {
        return $this->hasCurrentUserRoleName(self::ROLE_COURSE_MANAGER);
    }

    public function isCurrentUserAdmin(): bool
    {
        return $this->hasCurrentUserRoleName(self::ROLE_ADMINISTRATOR);
    }

    public function isCurrentUserTeacher(): bool
    {
        return $this->hasCurrentUserRoleName(self::ROLE_TEACHER);
    }

    public function isCurrentUserStudent(): bool
    {
        return $this->hasCurrentUserRoleName(self::ROLE_STUDENT);
    }

    public function getCourseManagerRoleId(): int
    {
        return $this->getCourseManagerRole()->getId();
    }

    public function getAdminRoleId(): int
    {
        return $this->getAdminRole()->getId();
    }

    public function getCourseManagerRole(): ?RoleEntity
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_COURSE_MANAGER
        ]);
    }

    public function getAdminRole(): ?RoleEntity
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_ADMINISTRATOR
        ]);
    }

    public function getTeacherRoleId(): int
    {
        return $this->getTeacherRole()->getId();
    }

    public function getTeacherRole(): ?RoleEntity
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_TEACHER
        ]);
    }

    public function getStudentRoleId(): int
    {
        return $this->getStudentRole()->getId();
    }

    public function getStudentRole(): ?RoleEntity
    {
        return $this->getRoleRepository()->findOneBy([
            'role' => self::ROLE_STUDENT
        ]);
    }

    public function getCurrentUser()
    {
        /** @var User $user */
        return $this->tokenStorage->getToken()->getUser();
    }

    public function getCurrentUserId(): int
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        return $user->getId();
    }

    protected function getRoleRepository(): RoleRepository
    {
        return $this->em->getRepository('OroUserBundle:Role');
    }

    protected function getUserRepository(): UserRepository
    {
        return $this->em->getRepository('OroUserBundle:User');
    }

    protected function hasCurrentUserRoleName(string $roleName): bool
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!is_object($user)) {
            return false;
        }
        foreach ($user->getRoles() as $role) {
            /** @var RoleEntity $role */
            if ($role->getRole() == $roleName) {
                return true;
            }
        }
        return false;
    }
}
