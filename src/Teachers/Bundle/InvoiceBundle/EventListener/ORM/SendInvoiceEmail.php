<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Helper\Invoice as InvoiceHelper;

class SendInvoiceEmail
{
    /**
     * @var InvoiceHelper
     */
    private $invoiceHelper;

    /**
     * @param InvoiceHelper $invoiceHelper
     */
    public function __construct(
        InvoiceHelper $invoiceHelper
    )
    {
        $this->invoiceHelper = $invoiceHelper;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $args->getObject();
        if (!$invoice instanceof Invoice) {
            return;
        }
        $this->invoiceHelper->sendEmailForInvoice($invoice);
    }
}
