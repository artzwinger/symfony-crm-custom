<?php

namespace Teachers\Bundle\BidBundle\Placeholder;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
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
     * @param object|null $assignment
     * @return bool
     */
    public function isAssignmentUpForBid(?object $assignment): bool
    {
        if (!$assignment instanceof Assignment) {
            return false;
        }
        $workflowItem = $this->workflowManager->getWorkflowItem($assignment, 'assignment_flow');
        if (!$workflowItem) {
            return false;
        }
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if ($currentStepName === Assignment::WORKFLOW_STEP_UP_FOR_BID) {
            return true;
        }
        return false;
    }
}
