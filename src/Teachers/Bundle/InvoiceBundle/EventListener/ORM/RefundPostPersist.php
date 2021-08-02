<?php

namespace Teachers\Bundle\InvoiceBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\UIBundle\Model\FlashBag;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Teachers\Bundle\InvoiceBundle\Entity\Refund;
use Teachers\Bundle\InvoiceBundle\Helper\PaymentGateway;

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
     * @var PaymentGateway
     */
    private $paymentGateway;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FlashBag
     */
    private $flashBag;

    /**
     * RefundPostUpdate constructor.
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     * @param PaymentGateway $paymentGateway
     * @param LoggerInterface $logger
     * @param FlashBag $flashBag
     */
    public function __construct(
        EntityManager $entityManager,
        ActivityManager $activityManager,
        PaymentGateway $paymentGateway,
        LoggerInterface $logger,
        FlashBag $flashBag
    )
    {
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
        $this->paymentGateway = $paymentGateway;
        $this->logger = $logger;
        $this->flashBag = $flashBag;
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
        try {
            $data = $this->paymentGateway->refund($payment->getTransaction(), $refund->getAmountRefunded());
            $gwResponse = @new SimpleXMLElement($data);
            if ((string)$gwResponse->result != 1) {
                throw new Exception($gwResponse->{'result-text'});
            }
            $refund->setRefunded(true);
            $this->entityManager->persist($refund);
            $this->entityManager->flush($refund);
        } catch (Exception $e) {
            $this->flashBag->add('error', 'Refund request to NMI was not successful: ' . $e->getMessage());
            $this->logger->critical($e->getMessage(), [
                'type' => 'refund',
                'transaction-id' => $payment->getTransaction(),
                'amount' => $refund->getAmountRefunded()
            ]);
        }
    }
}
