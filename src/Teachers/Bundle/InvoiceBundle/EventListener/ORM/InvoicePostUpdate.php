<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class InvoicePostUpdate
{
    /**
     * @var WorkflowManager $workflowManager
     */
    private $workflowManager;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param WorkflowManager $workflowManager
     * @param EntityManager $entityManager
     */
    public function __construct(
        WorkflowManager $workflowManager,
        EntityManager $entityManager
    )
    {
        $this->workflowManager = $workflowManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     * @throws ORMException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $args->getObject();
        if (!$invoice instanceof Invoice) {
            return;
        }
        $remaining = $invoice->getAmountRemaining();
        $workflow = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
        if ($workflow->getCurrentStep()->getName() === Invoice::WORKFLOW_STEP_PAID) {
            if ($remaining > 0) {
                $workflow = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
                $this->workflowManager->transit($workflow, 'reopen');
            }
            return;
        }
        $targetStepName = $remaining == 0 ? Invoice::WORKFLOW_STEP_PAID : Invoice::WORKFLOW_STEP_PARTIALLY_PAID;
        if ($workflow->getCurrentStep()->getName() === $targetStepName) {
            return;
        }
        $workflow->setCurrentStep(
            $workflow->getDefinition()->getStepByName($targetStepName)
        );
        $this->entityManager->persist($workflow);
        $this->entityManager->flush($workflow);
    }
}
