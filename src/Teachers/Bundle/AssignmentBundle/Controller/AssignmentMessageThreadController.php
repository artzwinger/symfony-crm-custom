<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread;
use Teachers\Bundle\UsersBundle\Helper\Role;

class AssignmentMessageThreadController extends AbstractController
{
    /**
     * @param AssignmentMessageThread $thread
     *
     * @return Response
     *
     * @Route("/view/{id}", name="teachers_assignment_message_thread_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessageThread/view.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_thread_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:AssignmentMessageThread"
     * )
     */
    public function viewAction(AssignmentMessageThread $thread): Response
    {
        $userId = $this->get('oro_security.token_accessor')->getUserId();
        $userNotSenderOrRecipient = $thread->getRecipientId() !== $userId && $thread->getSender()->getId() !== $userId;
        /** @var Role $roleHelper */
        $roleHelper = $this->get('teachers_users.helper.role');
        $userIsTutorOrStudent = $roleHelper->isCurrentUserTeacher() || $roleHelper->isCurrentUserStudent();
        if ($userNotSenderOrRecipient && $userIsTutorOrStudent) {
            $this->get('oro_ui.session.flash_bag')->add('error', 'You do not have access to this thread');
            return $this->redirectToRoute('oro_dashboard_index');
        }
        return $this->render('@TeachersAssignment/AssignmentMessageThread/view.html.twig', [
            'entity' => $thread
        ]);
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_message_thread_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessageThread/widget/info.html.twig")
     * @AclAncestor("teachers_assignment_message_thread_view")
     * @param AssignmentMessageThread $thread
     * @return array|RedirectResponse
     */
    public function infoAction(AssignmentMessageThread $thread)
    {
        $userId = $this->get('oro_security.token_accessor')->getUserId();
        $userNotSenderOrRecipient = $thread->getRecipientId() !== $userId && $thread->getSender()->getId() !== $userId;
        /** @var Role $roleHelper */
        $roleHelper = $this->get('teachers_users.helper.role');
        $userIsTutorOrStudent = $roleHelper->isCurrentUserTeacher() || $roleHelper->isCurrentUserStudent();
        if ($userNotSenderOrRecipient && $userIsTutorOrStudent) {
            $this->get('oro_ui.session.flash_bag')->add('error', 'You do not have access to this thread');
            return $this->redirectToRoute('oro_dashboard_index');
        }
        return [
            'entity' => $thread
        ];
    }
}
