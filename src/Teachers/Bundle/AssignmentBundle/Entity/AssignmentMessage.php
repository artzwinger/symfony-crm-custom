<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignmentMessage;

/**
 * @ORM\Entity()
 * @ORM\Table(name="teachers_assignment_message")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-envelope"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="assignment",
 *              "category"="assignment"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_assignment_message_activity_view",
 *              "acl"="teachers_assignment_message_view",
 *              "action_button_widget"="teachers_assignment_message_button",
 *              "action_link_widget"="teachers_assignment_message_link"
 *          },
 *          "grid"={
 *              "default"="assignment-messages-grid"
 *          }
 *      }
 * )
 */
class AssignmentMessage extends ExtendAssignmentMessage
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    protected $message;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $updatedBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_NOT_APPROVED = 'not_approved';

    const WORKFLOW_NAME = 'assignment_message_flow';

    const WORKFLOW_PENDING = 'pending';
    const WORKFLOW_APPROVED = 'approved';

    public static function getAvailableWorkflowSteps(): array
    {
        return [
            self::WORKFLOW_PENDING,
            self::WORKFLOW_APPROVED,
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => [
                'name' => 'Pending',
                'is_default' => true
            ],
            self::STATUS_APPROVED => [
                'name' => 'Approved',
                'is_default' => false
            ],
            self::STATUS_NOT_APPROVED => [
                'name' => 'Not approved',
                'is_default' => false
            ],
        ];
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets message
     *
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Sets message
     *
     * @param string $message
     *
     * @return self
     */
    public function setMessage(string $message): AssignmentMessage
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets user who have updated this comment
     *
     * @return User
     */
    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    /**
     * Sets user who have updated this comment
     *
     * @param User $updatedBy
     *
     * @return self
     */
    public function setUpdatedBy(User $updatedBy): AssignmentMessage
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get owner comment
     *
     * @return User
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Set the owner comment
     *
     * @param User $owner
     *
     * @return self
     */
    public function setOwner($owner = null): AssignmentMessage
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Gets organization
     *
     * @return Organization
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * Sets organization
     *
     * @param \Oro\Bundle\OrganizationBundle\Entity\Organization|null $organization
     *
     * @return self
     */
    public function setOrganization(Organization $organization = null): AssignmentMessage
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Gets creation date
     *
     * @return \DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets creation date
     *
     * @param \DateTime|null $createdAt
     *
     * @return self
     */
    public function setCreatedAt(DateTime $createdAt = null): AssignmentMessage
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets modification date
     *
     * @return \DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Sets a date update
     *
     * @param \DateTime|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(DateTime $updatedAt = null): AssignmentMessage
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Sets the date on which the comment is created
     *
     * @ORM\PrePersist
     * @throws \Exception
     */
    public function prePersist()
    {
        $this->createdAt = $this->createdAt ?: new DateTime('now', new DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Update the updatedAt when the updated comment
     *
     * @ORM\PreUpdate
     * @throws \Exception
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime('now', new DateTimeZone('UTC'));
    }

    public function isApproved(): bool
    {
        return $this->getStatus() && $this->getStatus()->getId() === self::STATUS_APPROVED;
    }

    public function isNotApproved(): bool
    {
        return $this->getStatus() && $this->getStatus()->getId() === self::STATUS_NOT_APPROVED;
    }
}
