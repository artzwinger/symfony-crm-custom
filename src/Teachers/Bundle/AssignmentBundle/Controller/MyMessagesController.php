<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Doctrine\ORM\ORMException;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread;

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
     * @throws ORMException
     */
    public function indexAction(): array
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $messages = $em->getRepository(AssignmentMessage::class)->findBy([
            'recipient' => $this->get('teachers_users.helper.role')->getCurrentUser(),
            'viewedByRecipient' => false
        ]);
        foreach ($messages as $message) {
            $message->setViewedByRecipient(true);
            $em->persist($message);
            $em->flush($message);
        }
        return [
            'entity_class' => AssignmentMessageThread::class
        ];
    }
}
