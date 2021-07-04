<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
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
     * PaymentPostPersist constructor.
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     */
    public function __construct(
        EntityManager $entityManager,
        ActivityManager $activityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
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
        $this->activityManager->addActivityTarget($payment, $invoice);
        $amountPaidAfterRefund = $payment->getAmountPaid() - $payment->getAmountRefunded();
        if ($amountPaidAfterRefund !== $payment->getAmountPaidAfterRefund()) {
            $payment->setAmountPaidAfterRefund($amountPaidAfterRefund);
            $this->entityManager->persist($payment);
            $this->entityManager->flush($payment);
        }
        if ($payment->getAmountPaid() > $invoice->getAmountRemaining()) {
            $payment->setAmountPaid($invoice->getAmountRemaining());
            $this->entityManager->persist($payment);
            $this->entityManager->flush($payment);
            return;
        }
        $payments = $invoice->getPayments();
        $paid = 0;
        foreach ($payments as $payment) {
            $paid += $payment->getAmountPaid();
        }
        $invoice->setAmountPaid($paid);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);
    }
}
