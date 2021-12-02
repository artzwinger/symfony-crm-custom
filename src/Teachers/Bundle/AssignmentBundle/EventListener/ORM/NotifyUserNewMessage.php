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
    const TYPE_PERSONAL = '0';
    const TYPE_CM_QUEUE = '1';
    const TYPE_APPROVAL_QUEUE = '2';

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
        if ($message->getStatus()->getId() === AssignmentMessage::STATUS_PENDING) {
            $recipientIds = $this->getCourseManagersAndAdminsIds();
            $this->notifyRecipients($recipientIds, self::TYPE_APPROVAL_QUEUE);
            return;
        } else if ($message->getStatus()->getId() !== AssignmentMessage::STATUS_APPROVED) {
            return;
        }
        $type = self::TYPE_PERSONAL;
        $recipient = $message->getRecipient();
        if (!$recipient) {
            $type = self::TYPE_CM_QUEUE;
        }
        $recipientIds = $recipient ? [$recipient->getId()] : $this->getCourseManagersAndAdminsIds();
        $this->notifyRecipients($recipientIds, $type);
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

    private function notifyRecipients(array $ids, string $type = self::TYPE_PERSONAL)
    {
        $payload = json_encode([
            'type' => $type
        ]);
        foreach ($ids as $id) {
            $this->websocketClient->publish('teachers/new_message/' . $id, $payload);
        }
    }
}
