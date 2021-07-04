<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class PaymentPostUpdate
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * PaymentPostUpdate constructor.
     * @param EntityManager $entityManager
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        EntityManager $entityManager,
        WorkflowManager $workflowManager
    )
    {
        $this->entityManager = $entityManager;
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Payment $payment */
        $payment = $args->getObject();
        if (!$payment instanceof Payment) {
            return;
        }
        $invoice = $payment->getInvoice();
        $payments = $invoice->getPayments();
        $paid = 0;
        foreach ($payments as $pm) {
            $paid += $pm->getAmountPaid() - $pm->getAmountRefunded();
        }
        $invoice->setAmountPaid($paid);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);
        if ($payment->isFullyRefunded() && !$payment->isStatusFullyRefunded()) {
            /** @var AbstractEnumValue $status */
            $status = $this->entityManager
                ->getRepository(ExtendHelper::buildEnumValueClassName('payment_status'))
                ->findOneBy(['id' => Payment::STATUS_FULLY_REFUNDED]);
            $payment->setStatus($status);
            $this->entityManager->persist($payment);
            $this->entityManager->flush($payment);
            $workflowItem = $this->workflowManager->getWorkflowItem($payment, 'payment_flow');
            $workflowItem->setCurrentStep($workflowItem->getDefinition()->getStepByName(Payment::WORKFLOW_STEP_FULLY_REFUNDED));
            $this->entityManager->persist($workflowItem);
            $this->entityManager->flush($workflowItem);
        }
        if ($payment->isPartiallyRefunded() && !$payment->isStatusPartiallyRefunded()) {
            /** @var AbstractEnumValue $status */
            $status = $this->entityManager
                ->getRepository(ExtendHelper::buildEnumValueClassName('payment_status'))
                ->findOneBy(['id' => Payment::STATUS_PARTIALLY_REFUNDED]);
            $payment->setStatus($status);
            $this->entityManager->persist($payment);
            $this->entityManager->flush($payment);
        }
    }
}
