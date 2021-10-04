<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

class CmMyMessagesController extends AbstractController
{
    /**
     * @Route(name="teachers_assignment_message_cm_my")
     * @Template("@TeachersAssignment/AssignmentMessage/cm_my_messages.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_cm_my",
     *      type="entity",
     *      permission="VIEW_CM_MY_MESSAGES",
     *      class="TeachersAssignmentBundle:AssignmentMessage"
     * )
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => AssignmentMessage::class
        ];
    }
}
