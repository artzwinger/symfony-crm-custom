<?php

namespace Teachers\Bundle\BidBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\BidBundle\Entity\Bid;

class BidPostUpdate
{
    /**
     * @var WorkflowManager $workflowManager
     */
    private $workflowManager;

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        WorkflowManager $workflowManager
    )
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Bid $bid */
        $bid = $args->getObject();
        if (!$bid instanceof Bid) {
            return;
        }
        if (!$this->isCurrentBidFlowStepWinning($bid)) {
            return;
        }
        $teacher = $bid->getTeacher();
        $assignment = $bid->getAssignment();
        $workflowItem = $this->workflowManager->getWorkflowItem($assignment, 'assignment_flow');
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if (in_array($currentStepName, ['complete', 'assigned'])) {
            return;
        }
        $data = $workflowItem->getData();
        $data->set('teacher', $teacher);
        $workflowItem->setData($data);
        $this->workflowManager->transit($workflowItem, 'assign');
    }

    protected function isCurrentBidFlowStepWinning(Bid $bid): bool
    {
        return $this->workflowManager->getWorkflowItem($bid, 'bid_flow')
                ->getCurrentStep()->getName() === 'winning';
    }
}
