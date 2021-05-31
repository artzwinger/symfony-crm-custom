<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class PaymentPostUpdate
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Payment $payment */
        $payment = $args->getObject();
        if (!$payment instanceof Payment) {
            return;
        }
        $invoice = $payment->getInvoice();
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
