<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignmentPrivateNote;

/**
 * @ORM\Entity()
 * @ORM\Table(name="teachers_assignment_priv_note")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-comment"
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
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="teachers_assignment_private_note_activity_view",
 *              "acl"="teachers_assignment_private_note_view",
 *              "action_button_widget"="teachers_assignment_private_note_button",
 *              "action_link_widget"="teachers_assignment_private_note_link"
 *          },
 *          "grid"={
 *              "default"="assignment-private-notes-grid"
 *          }
 *      }
 * )
 */
class AssignmentPrivateNote extends ExtendAssignmentPrivateNote
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

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets message
     *
     * @return string
     */
    public function getMessage()
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
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets user who have updated this comment
     *
     * @return User
     */
    public function getUpdatedBy()
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
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get owner comment
     *
     * @return User
     */
    public function getOwner()
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
    public function setOwner($owner = null): AssignmentPrivateNote
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
    public function setOrganization(Organization $organization = null): AssignmentPrivateNote
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
    public function setCreatedAt(DateTime $createdAt = null): AssignmentPrivateNote
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
    public function setUpdatedAt(DateTime $updatedAt = null): AssignmentPrivateNote
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
        $this->createdAt = $this->createdAt ? $this->createdAt : new DateTime('now', new DateTimeZone('UTC'));
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
}
