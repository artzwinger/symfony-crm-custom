<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
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
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
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
 *              "default"="assignments-grid",
 *              "context"="assignment-for-context-grid"
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
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $owner;
}
