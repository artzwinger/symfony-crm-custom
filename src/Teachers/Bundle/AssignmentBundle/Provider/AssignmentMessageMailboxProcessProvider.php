<?php

namespace Teachers\Bundle\AssignmentBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;
use Teachers\Bundle\AssignmentBundle\Entity\AsmgMsgMailboxProcessSettings;
use Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentMessageMailboxProcessType;

/**
 * Registers convert to Assignment Message mailbox email process.
 * Actual implementation of this process can be found in processes.yml of this bundle.
 */
class AssignmentMessageMailboxProcessProvider implements MailboxProcessProviderInterface
{
    const PROCESS_DEFINITION_NAME = 'convert_mailbox_email_to_assignment_message';

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return AsmgMsgMailboxProcessSettings::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType(): string
    {
        return AssignmentMessageMailboxProcessType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'teachers.assignment.mailbox.process.label';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(Mailbox $mailbox = null): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessDefinitionName(): string
    {
        return self::PROCESS_DEFINITION_NAME;
    }
}
