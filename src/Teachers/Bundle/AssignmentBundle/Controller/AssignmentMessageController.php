<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\CommentBundle\Entity\Manager\CommentApiManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread;
use Teachers\Bundle\AssignmentBundle\Helper\Messages;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentMessageController extends AbstractController
{
    /**
     * @param AssignmentMessage $assignmentMessage
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_message_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template
     * @Acl(
     *      id="teachers_assignment_message_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     */
    public function viewAction(AssignmentMessage $assignmentMessage): array
    {
        if (!$this->canViewUnapprovedMessages($assignmentMessage) && !$assignmentMessage->isApproved()) {
            $translator = $this->get('translator');
            $assignmentMessage->setMessage($assignmentMessage->isNotApproved()
                ? $translator->trans('teachers.assignment.message.not_approved_message')
                : $translator->trans('teachers.assignment.message.approval_pending_message'));
        }

        return [
            'entity' => $assignmentMessage
        ];
    }

    /**
     * @Route(name="teachers_assignment_message_index", options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessage/index.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_index",
     *      type="entity",
     *      permission="VIEW_MESSAGES_APPROVAL_QUEUE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => AssignmentMessage::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_message_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessage/widget/info.html.twig")
     * @AclAncestor("teachers_assignment_message_view")
     * @param AssignmentMessage $assignmentMessage
     * @return array
     */
    public function infoAction(AssignmentMessage $assignmentMessage): array
    {
        if (!$this->canViewUnapprovedMessages($assignmentMessage) && !$assignmentMessage->isApproved()) {
            $translator = $this->get('translator');
            $assignmentMessage->setMessage($assignmentMessage->isNotApproved()
                ? $translator->trans('teachers.assignment.message.not_approved_message')
                : $translator->trans('teachers.assignment.message.approval_pending_message'));
        }

        return [
            'entity' => $assignmentMessage
        ];
    }

    /**
     * @Route("/update/{id}", name="teachers_assignment_message_update", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_edit",
     *      type="entity",
     *      permission="EDIT",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @param AssignmentMessage $assignmentMessage
     * @return array|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function updateAction(AssignmentMessage $assignmentMessage)
    {
        $result = $this->update($assignmentMessage);
        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_assignment_message_update', [
                'id' => $assignmentMessage->getId()
            ]);
        }
        $isPost = $this->get('request_stack')->getCurrentRequest()->isMethod('POST');
        if ($isPost && $assignmentMessage->getStatus()->getId() !== AssignmentMessage::STATUS_PENDING) {
            /** @var WorkflowManager $wfm */
            $wfm = $this->get('oro_workflow.manager');
            $item = $wfm->getWorkflowItem($assignmentMessage, AssignmentMessage::WORKFLOW_NAME);
            $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_REFRESH);
        }
        return $result;
    }

    /**
     * @Route("/create", name="teachers_assignment_message_create", options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function createAction()
    {
        $message = new AssignmentMessage();
        $message->setOwner($this->get('teachers_users.helper.role')->getCurrentUser());
        $result = $this->update($message);
        $this->getMessagesHelper()->autoApproveIfAllowed($message);

        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_assignment_message_create');
        }
        return $result;
    }

    /**
     * @Route("/send_to_tutor/{assignmentId}/{threadId}",
     *     name="teachers_assignment_message_send_to_tutor",
     *     requirements={"assignmentId"="\d+", "threadId"="\d+"},
     *     options={"expose"=true},
     *     defaults={"threadId"=0})
     * @ParamConverter(name="assignment", options={"id": "assignmentId"})
     * @ParamConverter(name="thread", options={"id": "threadId"}, isOptional=true)
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @param Assignment $assignment
     * @param AssignmentMessageThread|null $thread
     * @return array|mixed|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws WorkflowException
     */
    public function sendToTutor(Assignment $assignment, AssignmentMessageThread $thread = null)
    {
        $tutor = $assignment->getTeacher();
        $message = new AssignmentMessage();
        $message->setRecipient($tutor);
        $message->setAssignment($assignment);
        $message->setStatus($this->getMessagesHelper()->getMessageStatusPending());
        $message->setOwner($this->get('teachers_users.helper.role')->getCurrentUser());
        if (!$thread) {
            $thread = $this->createThread($assignment, $tutor);
        }
        $message->setThread($thread);
        $result = $this->update($message);
        $this->getMessagesHelper()->autoApproveIfAllowed($message);
        $this->getMessagesHelper()->updateThreadLatestMessage($message);

        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_assignment_message_send_to_tutor', [
                'assignmentId' => $assignment->getId(),
                'threadId' => $thread ? $thread->getId() : 0
            ]);
        }
        return $result;
    }

    /**
     * @Route("/send_to_student/{assignmentId}/{threadId}",
     *     name="teachers_assignment_message_send_to_student",
     *     requirements={"assignmentId"="\d+", "threadId"="\d+"},
     *     options={"expose"=true},
     *     defaults={"threadId"=0})
     * @ParamConverter(name="assignment", options={"id": "assignmentId"})
     * @ParamConverter(name="thread", options={"id": "threadId"}, isOptional=true)
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @param Assignment $assignment
     * @param AssignmentMessageThread|null $thread
     * @return array|mixed|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws WorkflowException
     */
    public function sendToStudent(Assignment $assignment, AssignmentMessageThread $thread = null)
    {
        $student = $assignment->getStudent();
        $message = new AssignmentMessage();
        $message->setRecipient($student);
        $message->setAssignment($assignment);
        $message->setStatus($this->getMessagesHelper()->getMessageStatusPending());
        $message->setOwner($this->get('teachers_users.helper.role')->getCurrentUser());
        if (!$thread) {
            $thread = $this->createThread($assignment, $student);
        }
        $message->setThread($thread);
        $result = $this->update($message);
        $this->getMessagesHelper()->autoApproveIfAllowed($message);
        $this->getMessagesHelper()->updateThreadLatestMessage($message);

        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_assignment_message_send_to_student', [
                'assignmentId' => $assignment->getId(),
                'threadId' => $thread ? $thread->getId() : 0
            ]);
        }
        return $result;
    }

    /**
     * @Route("/send_to_course_manager/{assignmentId}/{threadId}",
     *     name="teachers_assignment_message_send_to_coursemanager",
     *     requirements={"assignmentId"="\d+", "threadId"="\d+"},
     *     options={"expose"=true},
     *     defaults={"threadId"=0})
     * @ParamConverter(name="assignment", options={"id": "assignmentId"})
     * @ParamConverter(name="thread", options={"id": "threadId"}, isOptional=true)
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @param Assignment $assignment
     * @param AssignmentMessageThread|null $thread
     * @return array|mixed|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws WorkflowException
     */
    public function sendToCourseManager(Assignment $assignment, AssignmentMessageThread $thread = null)
    {
        $message = new AssignmentMessage();
        $message->setAssignment($assignment);
        $message->setStatus($this->getMessagesHelper()->getMessageStatusPending());
        if (!$thread) {
            $thread = $this->createThread($assignment);
        }
        $message->setThread($thread);
        $result = $this->update($message);
        $this->getMessagesHelper()->autoApprove($message);
        $this->getMessagesHelper()->updateThreadLatestMessage($message);

        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_assignment_message_send_to_coursemanager', [
                'assignmentId' => $assignment->getId(),
                'threadId' => $thread ? $thread->getId() : 0
            ]);
        }

        return $result;
    }

    /**
     * @Route("/respond/{threadId}",
     *     name="teachers_thread_respond",
     *     requirements={"threadId"="\d+"},
     *     options={"expose"=true})
     * @ParamConverter(name="thread", options={"id": "threadId"}, isOptional=true)
     * @Template("@TeachersAssignment/AssignmentMessage/update.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @param AssignmentMessageThread $thread
     * @return array|mixed|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws WorkflowException
     */
    public function respondAction(AssignmentMessageThread $thread)
    {
        $userId = $this->get('oro_security.token_accessor')->getUserId();

        $userIsSender = $thread->getSender()->getId() === $userId;
        $userIsRecipient = $thread->getRecipientId() === $userId;
        $userNotSenderOrRecipient = !$userIsRecipient && !$userIsSender;

        /** @var Role $roleHelper */
        $roleHelper = $this->get('teachers_users.helper.role');
        $userIsTutorOrStudent = $roleHelper->isCurrentUserTeacher() || $roleHelper->isCurrentUserStudent();
        if ($userNotSenderOrRecipient && $userIsTutorOrStudent) {
            $this->get('oro_ui.session.flash_bag')->add('error', 'You do not have access to this thread');
            return $this->redirectToRoute('oro_dashboard_index');
        }
        $message = new AssignmentMessage();
        $message->setAssignment($thread->getAssignment());
        $message->setThread($thread);
        $message->setStatus($this->getMessagesHelper()->getMessageStatusPending());
        $message->setOwner($this->get('teachers_users.helper.role')->getCurrentUser());
        $recipient = $thread->getRecipient();
        if ($userIsRecipient || ($recipient === null && !$userIsSender)) {
            $recipient = $thread->getSender();
        }
        $message->setRecipient($recipient);
        $result = $this->update($message);
        $this->getMessagesHelper()->autoApproveIfAllowed($message);
        $this->getMessagesHelper()->updateThreadLatestMessage($message);

        if (is_array($result)) {
            $result['formAction'] = $this->generateUrl('teachers_thread_respond', [
                'threadId' => $thread->getId()
            ]);
        }

        return $result;
    }

    /**
     * @Route(
     *     "/delete/{id}",
     *     name="teachers_assignment_message_delete",
     *     requirements={"id"="\d+"},
     *     methods={"DELETE"},
     *     options={"expose"=true}
     * )
     * @Acl(
     *      id="teachers_assignment_message_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     * @CsrfProtection()
     * @param AssignmentMessage $assignment
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(AssignmentMessage $assignment): JsonResponse
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $em->remove($assignment);
        $em->flush();

        return new JsonResponse('', Response::HTTP_OK);
    }

    /**
     * @Route(
     *     "/approve/{id}",
     *     name="teachers_assignment_message_approve",
     *     requirements={"id"="\d+"},
     *     methods={"POST"},
     *     options={"expose"=true}
     * )
     * @AclAncestor("teachers_assignment_message_edit")
     * @CsrfProtection()
     * @param AssignmentMessage $assignmentMessage
     * @return JsonResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function approveAction(AssignmentMessage $assignmentMessage): JsonResponse
    {
        /** @var WorkflowManager $wfm */
        $wfm = $this->get('oro_workflow.manager');
        $item = $wfm->getWorkflowItem($assignmentMessage, AssignmentMessage::WORKFLOW_NAME);
        if (!$item) {
            $item = $wfm->startWorkflow(AssignmentMessage::WORKFLOW_NAME, $assignmentMessage);
        }
        $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_APPROVE);
        return new JsonResponse(['successful' => true]);
    }

    /**
     * @Route(
     *     "/unapprove/{id}",
     *     name="teachers_assignment_message_unapprove",
     *     requirements={"id"="\d+"},
     *     methods={"POST", "GET"},
     *     options={"expose"=true}
     * )
     * @AclAncestor("teachers_assignment_message_edit")
     * @Template("@TeachersAssignment/AssignmentMessage/update_comment.html.twig")
     * @CsrfProtection()
     * @param AssignmentMessage $message
     * @return array|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function unapproveAction(AssignmentMessage $message)
    {
        if ($this->isPostRequest() && !$message->isNotApproved()) {
            /** @var WorkflowManager $wfm */
            $wfm = $this->get('oro_workflow.manager');
            $item = $wfm->getWorkflowItem($message, AssignmentMessage::WORKFLOW_NAME);
            if (!$item) {
                $item = $wfm->startWorkflow(AssignmentMessage::WORKFLOW_NAME, $message);
            }
            $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_UNAPPROVE);
        }
        $response = $this->update($message);
        $response['assignment_message'] = $message;
        return $response;
    }

    /**
     * @Route(
     *     "/edit_approve/{id}",
     *     name="teachers_assignment_message_edit_approve",
     *     requirements={"id"="\d+"},
     *     methods={"POST", "GET"},
     *     options={"expose"=true}
     * )
     * @AclAncestor("teachers_assignment_message_edit")
     * @Template("@TeachersAssignment/AssignmentMessage/edit_approve.html.twig")
     * @CsrfProtection()
     * @param AssignmentMessage $message
     * @return array|RedirectResponse
     * @throws ForbiddenTransitionException
     * @throws InvalidTransitionException
     * @throws WorkflowException
     */
    public function editApproveAction(AssignmentMessage $message)
    {
        if ($this->get('request_stack')->getCurrentRequest()->isMethod('POST')) {
            /** @var WorkflowManager $wfm */
            $wfm = $this->get('oro_workflow.manager');
            $item = $wfm->getWorkflowItem($message, AssignmentMessage::WORKFLOW_NAME);
            if (!$item) {
                $item = $wfm->startWorkflow(AssignmentMessage::WORKFLOW_NAME, $message);
            }
            $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_APPROVE);
        }
        $response = $this->update($message);
        $response['assignment_message'] = $message;
        return $response;
    }

    /**
     * Get entity Manager
     *
     * @return CommentApiManager
     */
    public function getCommentManager()
    {
        return $this->get('oro_comment.comment.api_manager');
    }

    private function getMessagesHelper(): Messages
    {
        return $this->get('teachers_assignment.helper.messages');
    }

    /**
     * @param AssignmentMessage $entity
     * @return RedirectResponse|array
     */
    private function update(AssignmentMessage $entity)
    {
        /** @var FormFactory $factory */
        /** @var UpdateHandlerFacade $handler */
        $factory = $this->get('form.factory');
        $handler = $this->get('oro_form.update_handler');
        $form = $factory->create('Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentMessageType');
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.message.controller.assignment.saved.message')
        );
    }

    /**
     * @param AssignmentMessage $msg
     *
     * @return bool
     */
    protected function canViewUnapprovedMessages(AssignmentMessage $msg): bool
    {
        $helper = $this->container->get('teachers_users.helper.role');
        return $helper->isCurrentUserCourseManager() || $helper->getCurrentUserId() == $msg->getOwner()->getId();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    private function createThread(Assignment $assignment, User $recipient = null): ?AssignmentMessageThread
    {
        $thread = new AssignmentMessageThread();
        $thread->setRecipient($recipient);
        $thread->setAssignment($assignment);
        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($thread);
        $em->flush($thread);
        return $thread;
    }

    private function isPostRequest(): bool
    {
        return $this->get('request_stack')->getCurrentRequest()->isMethod('POST');
    }
}
