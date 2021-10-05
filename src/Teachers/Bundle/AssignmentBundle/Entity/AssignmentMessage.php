<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
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
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="assignment",
 *              "category"="assignment"
 *          },
 *          "grid"={
 *              "default"="assignment-messages-grid"
 *          }
 *      }
 * )
 */
class AssignmentMessage extends ExtendAssignmentMessage implements DatesAwareInterface
{
    use DatesAwareTrait;

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
     * @var string|null
     *
     * @ORM\Column(name="denial_reason", type="text", nullable=true)
     */
    protected $denialReason;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $updatedBy;

    /**
     * @var Assignment
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\Assignment")
     * @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignment;

    /**
     * @var AssignmentMessageThread|null
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $thread;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $recipient;

    /**
     * @ORM\Column(name="viewed_by_recipient", type="boolean", nullable=false)
     */
    protected $viewedByRecipient = false;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    const ENUM_NAME_STATUS = 'assignment_msg_status';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_NOT_APPROVED = 'not_approved';

    const WORKFLOW_NAME = 'assignment_message_flow';
    const WORKFLOW_TRANSITION_REFRESH = 'refresh';
    const WORKFLOW_TRANSITION_APPROVE = 'approve';
    const WORKFLOW_TRANSITION_UNAPPROVE = 'unapprove';
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
     * @return string|null
     */
    public function getDenialReason(): ?string
    {
        return $this->denialReason;
    }

    /**
     * @param string|null $denialReason
     */
    public function setDenialReason(?string $denialReason): void
    {
        $this->denialReason = $denialReason;
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
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    /**
     * @param User|null $recipient
     */
    public function setRecipient(?User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return bool
     */
    public function isViewedByRecipient(): bool
    {
        return $this->viewedByRecipient;
    }

    /**
     * @param bool $viewedByRecipient
     */
    public function setViewedByRecipient(bool $viewedByRecipient): void
    {
        $this->viewedByRecipient = $viewedByRecipient;
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
     * @param Organization|null $organization
     *
     * @return self
     */
    public function setOrganization(Organization $organization = null): AssignmentMessage
    {
        $this->organization = $organization;

        return $this;
    }

    public function getAssignment(): ?Assignment
    {
        return $this->assignment;
    }

    /**
     * @param Assignment|null $assignment
     */
    public function setAssignment(?Assignment $assignment): void
    {
        $this->assignment = $assignment;
    }

    /**
     * @return AssignmentMessageThread|null
     */
    public function getThread(): ?AssignmentMessageThread
    {
        return $this->thread;
    }

    /**
     * @param AssignmentMessageThread|null $thread
     */
    public function setThread(?AssignmentMessageThread $thread): void
    {
        $this->thread = $thread;
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
