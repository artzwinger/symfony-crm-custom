<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignment;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroup;

/**
 * @ORM\Entity(repositoryClass="Teachers\Bundle\AssignmentBundle\Entity\Repository\AssignmentRepository")
 * @ORM\Table(
 *      name="teachers_assignment",
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="teachers_assignment_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-clipboard"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="courseManager",
 *              "owner_column_name"="course_manager_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="assignment"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_assignment_activity_view",
 *              "acl"="teachers_assignment_view",
 *              "action_button_widget"="teachers_assignment_button",
 *              "action_link_widget"="teachers_assignment_link"
 *          },
 *          "grid"={
 *              "default"="teachers-assignments-grid"
 *          }
 *      }
 * )
 */
class Assignment extends ExtendAssignment implements DatesAwareInterface
{
    use DatesAwareTrait;
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
     * @var string|null $subject
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $subject;

    /**
     * @var string|null $description
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $description;

    /**
     * @var User $teacher
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="teacher_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $teacher;
    /**
     * @var User $student
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="student_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $student;
    /**
     * @var Collection|TeacherGroup[] $teacherGroups
     *
     * @ORM\ManyToMany(targetEntity="Teachers\Bundle\UsersBundle\Entity\TeacherGroup", inversedBy="assignments")
     * @ORM\JoinTable(name="teachers_asmg_to_tg",
     *      joinColumns={@ORM\JoinColumn(name="assignment_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="teacher_group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $teacherGroups;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="course_manager_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $courseManager;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    const STATUS_NEW = 'new';
    const STATUS_UP_FOR_BID = 'up_for_assignment';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_COMPLETE = 'complete';

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_NEW => [
                'name' => 'New',
                'is_default' => true
            ],
            self::STATUS_UP_FOR_BID => [
                'name' => 'Up for bid',
                'is_default' => true
            ],
            self::STATUS_ASSIGNED => [
                'name' => 'Assigned',
                'is_default' => true
            ],
            self::STATUS_COMPLETE => [
                'name' => 'Complete',
                'is_default' => true
            ],
        ];
    }

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
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string|null $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
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
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $teacher
     */
    public function setTeacher(User $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getStudent(): ?User
    {
        return $this->student;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $student
     */
    public function setStudent(User $student): void
    {
        $this->student = $student;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|\Teachers\Bundle\UsersBundle\Entity\TeacherGroup[]
     */
    public function getTeacherGroups()
    {
        return $this->teacherGroups;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|\Teachers\Bundle\UsersBundle\Entity\TeacherGroup[] $teacherGroups
     */
    public function setTeacherGroups($teacherGroups): void
    {
        $this->teacherGroups = $teacherGroups;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    public function getCourseManager(): ?User
    {
        return $this->courseManager;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $courseManager
     */
    public function setCourseManager(User $courseManager): void
    {
        $this->courseManager = $courseManager;
    }

    /**
     * @return \Oro\Bundle\OrganizationBundle\Entity\Organization
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param \Oro\Bundle\OrganizationBundle\Entity\Organization $organization
     */
    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
    }
}
