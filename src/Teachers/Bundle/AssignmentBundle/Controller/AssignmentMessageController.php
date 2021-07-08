<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\CommentBundle\Entity\Comment;
use Oro\Bundle\CommentBundle\Entity\Manager\CommentApiManager;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Oro\Bundle\WorkflowBundle\Exception\ForbiddenTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\InvalidTransitionException;
use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
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
        $result = $this->update($assignmentMessage, 'teachers_assignment_message_update');
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
        $result = $this->update($message, 'teachers_assignment_message_create');
        if ($message->getId()) {
            /** @var Role $roleHelper */
            $roleHelper = $this->get('teachers_users.helper.role');
            $senderStudent = $roleHelper->isCurrentUserStudent();
            $senderTeacher = $roleHelper->isCurrentUserTeacher();
            $approve = !$senderStudent && !$senderTeacher; // approve if sender is course manager or admin
            if ($approve) {
                /** @var WorkflowManager $wfm */
                $wfm = $this->get('oro_workflow.manager');
                $item = $wfm->getWorkflowItem($message, AssignmentMessage::WORKFLOW_NAME);
                $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_APPROVE);
            }
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
        $relationClass = 'Teachers_Bundle_AssignmentBundle_Entity_AssignmentMessage';
        if ($this->get('request_stack')->getCurrentRequest()->isMethod('POST')) {
            /** @var WorkflowManager $wfm */
            $wfm = $this->get('oro_workflow.manager');
            $item = $wfm->getWorkflowItem($message, AssignmentMessage::WORKFLOW_NAME);
            $wfm->transit($item, AssignmentMessage::WORKFLOW_TRANSITION_UNAPPROVE);
        }
        $comment = new Comment();
        $this->getCommentManager()->setRelationField($comment, $relationClass, $message->getId());
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'oro_comment_api',
            'Oro\Bundle\CommentBundle\Form\Type\CommentTypeApi'
        );
        $builder->setAction('teachers_assignment_message_unapprove');
        $form = $builder->getForm();
        $response = $handler->update(
            $comment,
            $form,
            $this->get('translator')->trans('teachers.assignment.message.controller.assignment.saved.message')
        );
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

    /**
     * @param AssignmentMessage $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(AssignmentMessage $entity, $action)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('teachers.assignment.message.controller.assignment.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder(
            'teachers_assignment_message_form',
            'Teachers\Bundle\AssignmentBundle\Form\Type\AssignmentMessageType'
        );
        $builder->setAction($action);

        return $builder->getForm();
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
}
