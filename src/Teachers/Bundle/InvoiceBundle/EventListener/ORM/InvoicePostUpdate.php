<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;
use Teachers\Bundle\InvoiceBundle\Helper\Invoice as InvoiceHelper;

class InvoicePostUpdate
{
    /**
     * @var WorkflowManager $workflowManager
     */
    private $workflowManager;
    /**
     * @var InvoiceHelper
     */
    private $invoiceHelper;

    /**
     * @param WorkflowManager $workflowManager
     * @param InvoiceHelper $invoiceHelper
     */
    public function __construct(
        WorkflowManager $workflowManager,
        InvoiceHelper $invoiceHelper
    )
    {
        $this->workflowManager = $workflowManager;
        $this->invoiceHelper = $invoiceHelper;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $args->getObject();
        if (!$invoice instanceof Invoice) {
            return;
        }
        $this->invoiceHelper->sendEmailForInvoice($invoice);
        $remaining = $invoice->getAmountRemaining();
        if ($invoice->getStatus()->getId() === Invoice::STATUS_PAID) {
            if ($remaining > 0) {
                $workflowItem = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
                $this->workflowManager->transit($workflowItem, 'reopen');
            }
            return;
        }
        if ($remaining != 0) {
            return;
        }
        $workflowItem = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
        $this->workflowManager->transit($workflowItem, 'make_paid');
    }
}
