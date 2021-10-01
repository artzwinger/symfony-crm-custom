<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread;

class AssignmentMessageThreadController extends AbstractController
{
    /**
     * @param AssignmentMessageThread $assignmentMessageThread
     *
     * @return array
     *
     * @Route("/view/{id}", name="teachers_assignment_message_thread_view", requirements={"id"="\d+"}, options={"expose"=true})
     * @Acl(
     *      id="teachers_assignment_message_thread_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="TeachersAssignmentBundle:AssignmentMessageThread"
     * )
     */
    public function viewAction(AssignmentMessageThread $assignmentMessageThread): array
    {
        return [
            'entity' => $assignmentMessageThread
        ];
    }

    /**
     * @Route(name="teachers_assignment_message_thread_index", options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessageThread/index.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_thread_index",
     *      type="entity",
     *      permission="VIEW_MESSAGES_APPROVAL_QUEUE",
     *      class="TeachersAssignmentBundle:AssignmentMessageThread"
     * )
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => AssignmentMessageThread::class
        ];
    }

    /**
     * @Route("/info/{id}", name="teachers_assignment_message_thread_info", requirements={"id"="\d+"}, options={"expose"=true})
     * @Template("@TeachersAssignment/AssignmentMessageThread/widget/info.html.twig")
     * @AclAncestor("teachers_assignment_message_thread_view")
     * @param AssignmentMessageThread $assignmentMessageThread
     * @return array
     */
    public function infoAction(AssignmentMessageThread $assignmentMessageThread): array
    {
        return [
            'entity' => $assignmentMessageThread
        ];
    }
}
