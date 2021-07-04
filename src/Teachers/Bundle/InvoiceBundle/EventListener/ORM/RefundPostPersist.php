<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Teachers\Bundle\InvoiceBundle\Entity\Refund;

class RefundPostPersist
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
     * RefundPostUpdate constructor.
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
        /** @var Refund $payment */
        $refund = $args->getObject();
        if (!$refund instanceof Refund) {
            return;
        }
        $payment = $refund->getPayment();
        $this->activityManager->addActivityTarget($refund, $payment);
        $refunds = $payment->getRefunds();
        $amountRefunded = 0;
        foreach ($refunds as $rf) {
            $amountRefunded += $rf->getAmountRefunded();
        }
        $payment->setAmountRefunded($amountRefunded);
        $payment->setAmountPaidAfterRefund($payment->getAmountPaid() - $amountRefunded);
        $this->entityManager->persist($payment);
        $this->entityManager->flush($payment);
    }
}
