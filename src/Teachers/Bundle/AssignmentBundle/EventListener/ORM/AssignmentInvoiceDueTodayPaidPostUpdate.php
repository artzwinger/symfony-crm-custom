<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AssignmentInvoiceDueTodayPaidPostUpdate
{
    const ASSIGNMENT_WORKFLOW_NAME = 'assignment_flow';
    const START_BIDDING_TRANSITION_NAME = 'start_accepting_bids';
    /**
     * @var bool $processed
     */
    private static $processed = false;
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
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (self::$processed || !$assignment instanceof Assignment) {
            return;
        }
        self::$processed = true;
        if (!$assignment->getInvoiceDueTodayPaid()) {
            return;
        }
        $item = $this->workflowManager->getWorkflowItem($assignment, self::ASSIGNMENT_WORKFLOW_NAME);
        if ($item) {
            $this->workflowManager->transitIfAllowed($item, self::START_BIDDING_TRANSITION_NAME);
        }
    }
}
