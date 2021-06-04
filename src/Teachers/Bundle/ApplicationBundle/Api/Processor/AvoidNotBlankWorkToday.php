<?php

namespace Teachers\Bundle\ApplicationBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class AvoidNotBlankWorkToday implements ProcessorInterface
{
    public function process(ContextInterface $context)
    {
        /** @var Application $application */
        $application = $context->getResult();
        if (!$application instanceof Application) {
            return;
        }
        $workToday = $application->getWorkToday();
        $application->setWorkToday($workToday == 'yes');
    }
}
