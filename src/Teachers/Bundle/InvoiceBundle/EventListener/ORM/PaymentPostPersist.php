<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class PaymentPostPersist
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    /**
     * @var ActivityManager
     */
    private $activityManager;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;
    /**
     * @var Payment
     */
    private $payment;

    /**
     * PaymentPostPersist constructor.
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     * @param WorkflowManager $workflowManager
     */
    public function __construct(
        EntityManager   $entityManager,
        ActivityManager $activityManager,
        WorkflowManager $workflowManager
    )
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Payment $payment */
        $payment = $args->getObject();
        if (!$payment instanceof Payment) {
            return;
        }
        $invoice = $payment->getInvoice();
        if (!$invoice) {
            return;
        }
        $this->payment = $payment;
        $this->activityManager->addActivityTarget($payment, $invoice);
        $this->initAmountPaidAfterRefund($payment);
        if ($payment->getAmountPaid() > $invoice->getAmountRemaining()) {
            $payment->setAmountPaid($invoice->getAmountRemaining());
        }
        $this->entityManager->persist($payment);
        $this->entityManager->flush($payment);
        $payments = $invoice->getPayments();
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->getAmountPaid();
        }
        $invoice->setAmountPaid($paid);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);
    }

    /**
     * @throws InvalidTransitionException
     * @throws ForbiddenTransitionException
     * @throws WorkflowException
     */
    public function postFlush()
    {
        if ($this->payment) {
            $this->updatePaymentStatus($this->payment);
        }
    }

    protected function initAmountPaidAfterRefund(Payment $payment)
    {
        $amountPaidAfterRefund = $payment->getAmountPaid() - $payment->getAmountRefunded();
        $payment->setAmountPaidAfterRefund($amountPaidAfterRefund);
    }

    /**
     * @throws InvalidTransitionException
     * @throws ForbiddenTransitionException
     * @throws WorkflowException
     */
    protected function updatePaymentStatus(Payment $payment)
    {
        $transition = Payment::TRANSITION_PARTIAL_PAYMENT;
        if ($payment->getInvoice()->getAmountRemaining() <= 0) {
            $transition = Payment::TRANSITION_PAID_IN_FULL;
        }
        $item = $this->workflowManager->getWorkflowItem($payment, Payment::WORKFLOW_NAME);
        if ($item->getCurrentStep()->getName() === Payment::WORKFLOW_STEP_CREATED) {
            $this->workflowManager->transit($item, $transition);
        }
    }
}
