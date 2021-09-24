<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

class MyMessagesController extends AbstractController
{
    /**
     * @Route(name="teachers_assignment_message_my")
     * @Template("@TeachersAssignment/AssignmentMessage/my_messages.html.twig")
     * @Acl(
     *      id="teachers_assignment_message_my",
     *      type="entity",
     *      permission="VIEW_MY_MESSAGES",
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
