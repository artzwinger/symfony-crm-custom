<?php

namespace Teachers\Bundle\UsersBundle\Helper;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\UserBundle\Entity\Repository\RoleRepository;
use Oro\Bundle\UserBundle\Entity\Repository\UserRepository;
use Oro\Bundle\UserBundle\Entity\Role as EntityRole;
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

    public function hasThisUserIdThisRole(int $userId, string $role): bool
    {
        $user = $this->getUserRepository()->find($userId);
        $role = $this->getRoleRepository()->findOneBy([
            'role' => $role
        ]);
        if (!$user || !$role) {
            return false;
        }
        $roleId = $role->getId();
        foreach ($user->getRoles() as $role) {
            /** @var EntityRole $role */
            if ($role->getId() == $roleId) {
                return true;
            }
        }
        return false;
    }

    public function isCurrentUserCourseManager(): bool
    {
        return $this->hasCurrentUserRole($this->getCourseManagerRoleId());
    }

    public function isCurrentUserTeacher(): bool
    {
        return $this->hasCurrentUserRole($this->getTeacherRoleId());
    }

    public function isCurrentUserStudent(): bool
    {
        return $this->hasCurrentUserRole($this->getStudentRoleId());
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

    public function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        return $user;
    }

    public function getCurrentUserId()
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

    protected function hasCurrentUserRole(int $roleId): bool
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!is_object($user)) {
            return false;
        }
        foreach ($user->getRoles() as $role) {
            /** @var EntityRole $role */
            if ($role->getId() == $roleId) {
                return true;
            }
        }
        return false;
    }
}
