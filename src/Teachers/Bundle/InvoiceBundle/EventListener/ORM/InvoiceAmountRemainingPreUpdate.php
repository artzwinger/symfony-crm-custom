<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class InvoiceAmountRemainingPreUpdate
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $args->getObject();
        if (!$invoice instanceof Invoice) {
            return;
        }
        $owed = $invoice->getAmountOwed();
        $paid = $invoice->getAmountPaid();
        $invoice->setAmountRemaining($owed - $paid);
    }
}
