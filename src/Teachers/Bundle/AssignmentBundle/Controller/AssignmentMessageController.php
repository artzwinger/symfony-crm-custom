<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Acl\Domain\DomainObjectWrapper;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

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
     * @AclAncestor("teachers_assignment_message_view")
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
     */
    public function updateAction(AssignmentMessage $assignmentMessage)
    {
        return $this->update($assignmentMessage, 'teachers_assignment_message_update');
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
     */
    public function createAction()
    {
        return $this->update(new AssignmentMessage(), 'teachers_assignment_message_create');
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
