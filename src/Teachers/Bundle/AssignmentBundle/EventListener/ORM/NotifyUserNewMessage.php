<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\SyncBundle\Client\WebsocketClientInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\UsersBundle\Helper\Role;

class NotifyUserNewMessage
{
    /**
     * @var WebsocketClientInterface
     */
    private $websocketClient;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var Role
     */
    private $roleHelper;

    /**
     * @param EntityManager $entityManager
     * @param Role $roleHelper
     * @param WebsocketClientInterface $websocketClient
     */
    public function __construct(
        EntityManager $entityManager,
        Role $roleHelper,
        WebsocketClientInterface $websocketClient
    )
    {
        $this->entityManager = $entityManager;
        $this->websocketClient = $websocketClient;
        $this->roleHelper = $roleHelper;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var AssignmentMessage $message */
        $message = $args->getObject();
        if (!$message instanceof AssignmentMessage) {
            return;
        }
        if ($message->getStatus()->getId() !== AssignmentMessage::STATUS_APPROVED) {
            return;
        }
        $recipient = $message->getRecipient();
        $recipientIds = $recipient ? [$recipient->getId()] : $this->getCourseManagersAndAdminsIds();
        $this->notifyRecipients($recipientIds);
    }

    private function getCourseManagersAndAdminsIds(): array
    {
        $admins = $this->roleHelper->getAdmins();
        $managers = $this->roleHelper->getCourseManagers();
        $ids = [];
        /** @var User $u */
        foreach ($admins as $u) {
            $ids[] = $u->getId();
        }
        foreach ($managers as $u) {
            $ids[] = $u->getId();
        }
        return array_unique($ids);
    }

    private function notifyRecipients(array $ids)
    {
        foreach ($ids as $id) {
            $this->websocketClient->publish('teachers/new_message/' . $id, '');
        }
    }
}
