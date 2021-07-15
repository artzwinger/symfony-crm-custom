<?php

namespace Teachers\Bundle\AssignmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
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
