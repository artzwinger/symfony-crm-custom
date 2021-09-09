<?php

namespace Teachers\Bundle\AssignmentBundle\Placeholder;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class PlaceholderFilter
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @param WorkflowManager $workflowManager
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
        if (!$workflowItem) {
            return false;
        }
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if ($currentStepName === Application::WORKFLOW_STEP_WORKING) {
            return !$application->getAssignment();
        }
        return false;
    }
}
