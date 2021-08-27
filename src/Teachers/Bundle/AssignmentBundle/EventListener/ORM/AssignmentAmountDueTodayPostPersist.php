<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class AssignmentAmountDueTodayPostPersist
{
    /**
     * @var bool $processed
     */
    private static $processed = false;
    /**
     * @var Assignment $assignment
     */
    private $assignment;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var ActivityManager
     */
    private $activityManager;

    public function __construct(
        EntityManager $entityManager,
        WorkflowManager $workflowManager,
        ActivityManager $activityManager
    )
    {
        $this->workflowManager = $workflowManager;
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (!$assignment instanceof Assignment) {
            return;
        }
        $this->assignment = $assignment;
    }

    /**
     * @throws Exception
     */
    public function postFlush()
    {
        if (self::$processed || !$this->assignment) {
            return;
        }
        self::$processed = true;
        if ($this->assignment->getAmountDueToday()) {
            $this->createInvoice();
            return;
        }
        $item = $this->workflowManager->getWorkflowItem($this->assignment, Assignment::WORKFLOW_NAME);
        if ($item->getCurrentStep()->getName() === Assignment::WORKFLOW_STEP_NEW) {
            $this->workflowManager->transitIfAllowed($item, Assignment::WORKFLOW_TRANSITION_START_BIDDING);
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    protected function createInvoice()
    {
        $assignment = $this->assignment;
        $invoice = new Invoice();
        $invoice->setAssignment($assignment);
        $invoice->setAmountOwed($assignment->getAmountDueToday());
        $invoice->setAmountPaid(0);
        $invoice->setAmountRemaining($invoice->getAmountOwed());
        $invoice->setDueToday(true);
        $dueDate = (new DateTime('now'))->modify('+1 days');
        $invoice->setDueDate($dueDate);
        if ($assignment->getStudent()) {
            $invoice->setStudent($assignment->getStudent());
        }
        if ($studentContact = $assignment->getStudentContact()) {
            $invoice->setStudentContact($studentContact);
        }
        if ($studentAccount = $assignment->getStudentAccount()) {
            $invoice->setStudentAccount($studentAccount);
        }
        /** @var AbstractEnumValue $rep */
        $rep = $this->entityManager
            ->getRepository(ExtendHelper::buildEnumValueClassName(Invoice::ENUM_REP_CODE))
            ->find($assignment->getRep()->getId());
        $invoice->setRep($rep);
        $this->activityManager->addActivityTarget($invoice, $assignment);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);
    }
}
