<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AssignmentSetToApplicationPostPersist
{
    /**
     * @var EntityManager
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
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (!$assignment instanceof Assignment) {
            return;
        }
        $app = $assignment->getApplication();
        $app->setAssignment($assignment);
        $this->entityManager->persist($app);
        $this->entityManager->flush($app);
    }
}
