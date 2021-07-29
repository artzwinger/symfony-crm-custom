<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
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
        $workflow = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
        if ($workflow->getCurrentStep()->getName() === Invoice::WORKFLOW_STEP_PAID) {
            if ($invoice->getAmountRemaining() > 0) {
                $workflow = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
                $this->workflowManager->transit($workflow, 'reopen');
            }
            return;
        }
        $targetStepName = $this->getTargetStepName($invoice);
        $targetStatusId = $this->getTargetStatusId($invoice);
        if ($invoice->getStatus()->getId() !== $targetStatusId) {
            /** @var AbstractEnumValue $status */
            $status = $this->entityManager
                ->getRepository(ExtendHelper::buildEnumValueClassName(Invoice::INTERNAL_STATUS_CODE))
                ->find($targetStatusId);
            $invoice->setStatus($status);
            $this->entityManager->persist($invoice);
            $this->entityManager->flush($invoice);
            return;
        }
        if ($workflow->getCurrentStep()->getName() === $targetStepName) {
            return;
        }
        $workflow->setCurrentStep(
            $workflow->getDefinition()->getStepByName($targetStepName)
        );
        $this->entityManager->persist($workflow);
        $this->entityManager->flush($workflow);
    }

    protected function getTargetStepName(Invoice $invoice): string
    {
        if ($invoice->getAmountRemaining() == 0) {
            return Invoice::WORKFLOW_STEP_PAID;
        }
        if ($invoice->getAmountPaid() > 0) {
            return Invoice::WORKFLOW_STEP_PARTIALLY_PAID;
        }
        return Invoice::WORKFLOW_STEP_UNPAID;
    }

    protected function getTargetStatusId(Invoice $invoice)
    {
        if ($invoice->getAmountRemaining() == 0) {
            return Invoice::STATUS_PAID;
        }
        if ($invoice->getAmountPaid() > 0) {
            return Invoice::STATUS_PARTIALLY_PAID;
        }
        return Invoice::STATUS_UNPAID;
    }
}
