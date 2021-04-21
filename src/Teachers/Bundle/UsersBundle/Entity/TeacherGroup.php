<?php

namespace Teachers\Bundle\UsersBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Teachers\Bundle\UsersBundle\Model\ExtendTeacherGroup;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\UsersBundle\Entity\Repository\TeacherGroupRepository")
 * @ORM\Table(
 *      name="teachers_group",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_group_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-clipboard"
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="users"
 *          },
 *          "grid"={
 *              "default"="teachers-groups-grid"
 *          }
 *      }
 * )
 */
class TeacherGroup extends ExtendTeacherGroup
{
    /**
     * @var int $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var string|null $title
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string|null $description
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $description;

    /**
     * @var Collection|User[] $teachers
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinTable(name="teachers_tg_to_usr",
     *      joinColumns={@ORM\JoinColumn(name="teacher_group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $teachers;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|\Oro\Bundle\UserBundle\Entity\User[]
     */
    public function getTeachers()
    {
        return $this->teachers;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|\Oro\Bundle\UserBundle\Entity\User[] $teachers
     */
    public function setTeachers($teachers): void
    {
        $this->teachers = $teachers;
    }

    /**
     * @return \Oro\Bundle\OrganizationBundle\Entity\Organization
     */
    public function getOwner(): ?Organization
    {
        return $this->owner;
    }

    /**
     * @param \Oro\Bundle\OrganizationBundle\Entity\Organization $owner
     */
    public function setOwner(Organization $owner): void
    {
        $this->owner = $owner;
    }
}
