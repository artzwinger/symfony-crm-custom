<?php

namespace Teachers\Bundle\AssignmentBundle\Placeholder;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class PlaceholderFilter
{
    /**
     * @var \Oro\Bundle\WorkflowBundle\Model\WorkflowManager
     */
    private $workflowManager;

    /**
     * @param \Oro\Bundle\WorkflowBundle\Model\WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param object|null $application
     * @return bool
     */
    public function isApplicationInWorking(?object $application): bool
    {
        if (!$application instanceof Application) {
            return false;
        }
        $workflowItem = $this->workflowManager->getWorkflowItem($application, 'application_flow');
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if ($currentStepName === Application::WORKFLOW_STEP_WORKING) {
            return true;
        }
        return false;
    }
}
