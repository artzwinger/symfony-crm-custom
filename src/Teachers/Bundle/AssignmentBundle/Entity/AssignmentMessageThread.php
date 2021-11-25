<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignmentMessageThread;

/**
 * @ORM\Entity()
 * @ORM\Table(name="teachers_message_thread")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeView="teachers_assignment_message_thread_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-envelope"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="sender",
 *              "owner_column_name"="sender_id",
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
class AssignmentMessageThread extends ExtendAssignmentMessageThread implements DatesAwareInterface
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
     * @var Assignment|null
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\Assignment")
     * @ORM\JoinColumn(name="assignment_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignment;

    /**
     * @var AssignmentMessage|null
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage")
     * @ORM\JoinColumn(name="first_message_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $firstMessage;

    /**
     * @var AssignmentMessage|null
     *
     * @ORM\ManyToOne(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage")
     * @ORM\JoinColumn(name="latest_message_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $latestMessage;

    /**
     * @var AssignmentMessage[]|Collection|null
     * @ORM\OneToMany(targetEntity="Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage", mappedBy="thread")
     */
    protected $messages;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $sender;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $recipient;

    /**
     * @var Organization|null
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;
    /**
     * @return int|null
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
     * @return Assignment|null
     */
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
     * @return AssignmentMessage|null
     */
    public function getFirstMessage(): ?AssignmentMessage
    {
        return $this->firstMessage;
    }

    /**
     * @param AssignmentMessage|null $firstMessage
     */
    public function setFirstMessage(?AssignmentMessage $firstMessage): void
    {
        $this->firstMessage = $firstMessage;
    }

    /**
     * @return AssignmentMessage|null
     */
    public function getLatestMessage(): ?AssignmentMessage
    {
        return $this->latestMessage;
    }

    /**
     * @param AssignmentMessage|null $latestMessage
     */
    public function setLatestMessage(?AssignmentMessage $latestMessage): void
    {
        $this->latestMessage = $latestMessage;
    }

    /**
     * @return Collection|AssignmentMessage[]|null
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Collection|AssignmentMessage[]|null $messages
     */
    public function setMessages($messages): void
    {
        $this->messages = $messages;
    }

    /**
     * @return User|null
     */
    public function getSender(): ?User
    {
        return $this->sender;
    }

    /**
     * @param User|null $sender
     */
    public function setSender(?User $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return User|null
     */
    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function getRecipientId(): ?int
    {
        $rec = $this->getRecipient();
        return $rec ? $rec->getId() : 0;
    }

    public function isThreadRecipientCourseManager(): bool
    {
        return $this->getRecipientId() === 0;
    }

    /**
     * @param User|null $recipient
     */
    public function setRecipient(?User $recipient): void
    {
        $this->recipient = $recipient;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }
}
