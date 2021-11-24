<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Model\ExtendAssignmentMessageMailboxProcessSettings;

/**
 * Class name should be shorter than 30 symbols
 * @ORM\Entity
 * @Config(
 *      mode="hidden"
 * )
 */
class AsmgMsgMailboxProcessSettings extends ExtendAssignmentMessageMailboxProcessSettings
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="case_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'assignment_message';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
