<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class TransitNewAssignmentUpForBid
{
    const ASSIGNMENT_WORKFLOW_NAME = 'assignment_flow';
    const START_BIDDING_TRANSITION_NAME = 'start_accepting_bids';
    /**
     * @var bool $processed
     */
    private static $processed = false;
    /**
     * @var Assignment $assignment
     */
    private $assignment;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    public function __construct(
        WorkflowManager $workflowManager
    )
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (!$assignment instanceof Assignment) {
            return;
        }
        $this->assignment = $assignment;
    }

    /**
     * @throws Exception
     */
    public function postFlush()
    {
        if (self::$processed || !$this->assignment) {
            return;
        }
        self::$processed = true;
        if (!$this->assignment->getAmountDueToday()) {
            return;
        }
        $item = $this->workflowManager->getWorkflowItem($this->assignment, self::ASSIGNMENT_WORKFLOW_NAME);
        if ($item) {
            $this->workflowManager->transitIfAllowed($item, self::START_BIDDING_TRANSITION_NAME);
        }
    }
}
