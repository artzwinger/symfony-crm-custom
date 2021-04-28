<?php

namespace Teachers\Bundle\AssignmentBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class MyCoursesController extends AbstractController
{
    /**
     * @Route(name="teachers_assignment_my_courses")
     * @Template("@TeachersAssignment/AssignmentMy/index.html.twig")
     * @Acl(
     *      id="teachers_assignment_my_courses",
     *      type="entity",
     *      permission="VIEW_MY_COURSES",
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
