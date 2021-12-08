<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
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
     * @var Role
     */
    private $roleHelper;

    /**
     * @param Role $roleHelper
     * @param WebsocketClientInterface $websocketClient
     */
    public function __construct(
        Role $roleHelper,
        WebsocketClientInterface $websocketClient
    )
    {
        $this->websocketClient = $websocketClient;
        $this->roleHelper = $roleHelper;
    }

    /**
     * Notify Course Managers and Admins about new message in the approval queue
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var AssignmentMessage $message */
        $message = $args->getObject();
        if ($message instanceof AssignmentMessage && $message->isPending()) {
            $this->notifyRecipients($this->getCourseManagersAndAdminsIds(), self::TYPE_APPROVAL_QUEUE);
        }
    }

    /**
     * Notify Students/Tutors about new approved message in their inbox
     * Notify CM and Admins about new message from Student/Tutor
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var AssignmentMessage $message */
        $message = $args->getObject();
        if ($message instanceof AssignmentMessage && $message->isApproved()) {
            $recipient = $message->getRecipient();
            $type = $recipient ? self::TYPE_PERSONAL : self::TYPE_CM_QUEUE;
            $recipientIds = $recipient ? [$recipient->getId()] : $this->getCourseManagersAndAdminsIds();
            $this->notifyRecipients($recipientIds, $type);
        }
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
