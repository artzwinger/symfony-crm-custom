<?php

namespace Teachers\Bundle\AssignmentBundle\Action;

use Oro\Bundle\EmailBundle\Workflow\Action\SendEmailTemplate;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

/**
 * Workflow action that sends emails based on passed templates
 */
class SendMessageApprovedEmail extends SendEmailTemplate
{
    /**
     * {@inheritDoc}
     */
    protected function executeAction($context): void
    {
        /** @var AssignmentMessage $message */
        $message = $this->contextAccessor->getValue($context, $this->options['entity']);
        $this->options['to'] = [];
        if ($rec = $message->getRecipient()) {
            $this->options['recipients'] = [ $rec ];
        }
        parent::executeAction($context);
    }
}
