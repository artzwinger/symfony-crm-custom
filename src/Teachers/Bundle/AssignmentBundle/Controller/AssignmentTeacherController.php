<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AssignmentTeacherController extends AbstractController
{
    /**
     * @Route(name="teachers_assignment_teacher_index")
     * @Template("@TeachersAssignment/AssignmentTeacher/index.html.twig")
     * @Acl(
     *      id="teachers_assignment_teacher_index",
     *      type="entity",
     *      permission="VIEW_TEACHERS_QUEUE",
     *      class="TeachersAssignmentBundle:Assignment"
     * )
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => Assignment::class
        ];
    }
}
