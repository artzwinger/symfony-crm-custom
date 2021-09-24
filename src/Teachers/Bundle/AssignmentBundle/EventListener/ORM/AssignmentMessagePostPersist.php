<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\ORMException;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

class AssignmentMessagePostPersist
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
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
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var AssignmentMessage $message */
        $message = $args->getObject();
        if (!$message instanceof AssignmentMessage) {
            return;
        }
        $assignment = $message->getAssignment();
        if ($assignment) {
            $message->setAssignment($assignment);
            $this->entityManager->persist($assignment);
            $this->entityManager->flush($assignment);
        }
    }
}
