<?php

namespace Teachers\Bundle\InvoiceBundle\Placeholder;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class PlaceholderFilter
{
    /**
     * @var WorkflowManager
     */
    private $workflowManager;

    /**
     * @param WorkflowManager $workflowManager
     */
    public function __construct(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param object|null $assignment
     * @return bool
     */
    public function isAssignmentUpForInvoice(?object $assignment): bool
    {
        if (!$assignment instanceof Assignment) {
            return false;
        }
        $workflowItem = $this->workflowManager->getWorkflowItem($assignment, 'assignment_flow');
        if (!$workflowItem) {
            return false;
        }
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if ($currentStepName === Assignment::WORKFLOW_STEP_ASSIGNED) {
            return true;
        }
        return false;
    }

    /**
     * @param object|null $invoice
     * @return bool
     */
    public function isInvoiceUpForPayment(?object $invoice): bool
    {
        /** @var Invoice $invoice */
        if (!$invoice instanceof Invoice) {
            return false;
        }
        $workflowItem = $this->workflowManager->getWorkflowItem($invoice, 'invoice_flow');
        if (!$workflowItem) {
            return false;
        }
        $currentStepName = $workflowItem->getCurrentStep()->getName();
        if ($currentStepName === Invoice::WORKFLOW_STEP_UNPAID) {
            return true;
        }
        return false;
    }
}
