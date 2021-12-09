<?php

namespace Teachers\Bundle\AssignmentBundle\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\UsersBundle\Helper\Role;

/**
 */
class Messages
{
    /**
     * @var Role
     */
    private $roleHelper;
    /**
     * @var WorkflowManager
     */
    private $workflowManager;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param Role $roleHelper
     * @param WorkflowManager $workflowManager
     * @param EntityManager $entityManager
     */
    public function __construct(
        Role            $roleHelper,
        WorkflowManager $workflowManager,
        EntityManager   $entityManager
    )
    {
        $this->roleHelper = $roleHelper;
        $this->workflowManager = $workflowManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws InvalidTransitionException
     * @throws ForbiddenTransitionException
     * @throws WorkflowException
     */
    public function autoApproveIfAllowed(AssignmentMessage $message)
    {
        if ($this->availableForAutoApprove($message)) {
            $this->autoApprove($message);
        }
    }

    public function availableForAutoApprove(AssignmentMessage $message): bool
    {
        if (!$message->getId()) {
            return false;
        }
        $recipient = $message->getRecipient();
        if (!$recipient) {
            return true;
        }
        $recipientIsCourseManagerOrAdmin =
            $this->roleHelper->hasUserOneOfRoleNames($recipient, [Role::ROLE_ADMINISTRATOR, Role::ROLE_COURSE_MANAGER]);
        if ($recipientIsCourseManagerOrAdmin) {
            return true;
        }
        $sender = $message->getOwner();
        if ($sender) {
            return $this->roleHelper->hasUserOneOfRoleNames($sender, [Role::ROLE_ADMINISTRATOR, Role::ROLE_COURSE_MANAGER]);
        }
        return !$this->roleHelper->isCurrentUserStudent() && !$this->roleHelper->isCurrentUserTeacher();
    }

    /**
     * @throws InvalidTransitionException
     * @throws ForbiddenTransitionException
     * @throws WorkflowException
     */
    public function autoApprove(AssignmentMessage $message)
    {
        if (!$message->getId()) {
            return;
        }
        $item = $this->workflowManager->getWorkflowItem($message, AssignmentMessage::WORKFLOW_NAME);
        if (!$item) {
            $item = $this->workflowManager->startWorkflow(AssignmentMessage::WORKFLOW_NAME, $message);
        }
        $this->workflowManager->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_APPROVE);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function updateThreadLatestMessage(AssignmentMessage $message)
    {
        if (!$message->getId()) {
            return;
        }
        $thread = $message->getThread();
        $thread->setLatestMessage($message);
        if (!$thread->getFirstMessage()) {
            $thread->setFirstMessage($message);
        }
        $this->entityManager->persist($thread);
        $this->entityManager->flush($thread);
    }

    /**
     * @return object|AbstractEnumValue|null
     */
    public function getMessageStatusPending()
    {
        /** @var EnumValueRepository $enumRepo */
        $className = ExtendHelper::buildEnumValueClassName(AssignmentMessage::ENUM_NAME_STATUS);
        $enumRepo = $this->entityManager->getRepository($className);
        return $enumRepo->findOneBy([
            'id' => AssignmentMessage::STATUS_PENDING
        ]);
    }
}
