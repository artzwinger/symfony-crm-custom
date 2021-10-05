<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

class CmGeneralMessagesController extends AbstractController
{
    /**
     * @Route(name="teachers_assignment_message_cm_general")
     * @Template("@TeachersAssignment/AssignmentMessage/cm_general_messages.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_cm_general",
     *      type="entity",
     *      permission="VIEW_CM_GENERAL_MESSAGES",
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
