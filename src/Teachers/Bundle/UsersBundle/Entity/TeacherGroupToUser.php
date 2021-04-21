<?php

namespace Teachers\Bundle\UsersBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\UsersBundle\Model\ExtendTeacherGroupToUser;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\UsersBundle\Entity\Repository\TeacherGroupToUserRepository")
 * @ORM\Table(name="teachers_tg_to_usr")
 */
class TeacherGroupToUser extends ExtendTeacherGroupToUser
{
    /**
     * @var int|null $teacherGroup
     * @ORM\Id
     * @ORM\Column(name="teacher_group_id", type="integer", nullable=false)
     */
    protected $teacherGroup;
    /**
     * @var \Oro\Bundle\UserBundle\Entity\User
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * @return int|null
     */
    public function getTeacherGroup(): ?int
    {
        return $this->teacherGroup;
    }

    /**
     * @param int $teacherGroup
     */
    public function setTeacherGroup(int $teacherGroup): void
    {
        $this->teacherGroup = $teacherGroup;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
