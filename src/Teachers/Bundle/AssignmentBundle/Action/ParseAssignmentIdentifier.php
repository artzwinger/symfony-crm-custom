<?php

namespace Teachers\Bundle\AssignmentBundle\Action;

use Oro\Component\Action\Action\AbstractAction;
use Oro\Component\Action\Exception\InvalidParameterException;

/**
 * Workflow action that sends emails based on passed templates
 */
class ParseAssignmentIdentifier extends AbstractAction
{
    /**
     * {@inheritDoc}
     */
    protected function executeAction($context): string
    {
        $value = $this->contextAccessor->getValue($context, 'value');
        preg_match('/Assignment #([a-zA-Z0-9-_]+)/', $value, $matches);
        if (count($matches) > 1) {
            return $matches[1];
        }
        return '';
    }

    public function initialize(array $options)
    {
        if (empty($options['value'])) {
            throw new InvalidParameterException('Value parameter is required');
        }

        return $this;
    }
}
