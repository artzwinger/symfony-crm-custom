<?php

namespace Teachers\Bundle\UsersBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\UsersBundle\Helper\Role as RoleHelper;

class UserController extends AbstractController
{
    /**
     * Create user form
     *
     * @Route("/create_teacher", name="teachers_teacher_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update_teacher.html.twig")
     * @Acl(
     *      id="oro_user_user_create",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="CREATE"
     * )
     */
    public function createTeacherAction()
    {
        /** @var User $user */
        $user = $this->get('oro_user.manager')->createUser();
        $user->setRoles([
            $this->getRoleHelper()->getTeacherRole()
        ]);

        return $this->update($user);
    }

    /**
     * Create user form
     *
     * @Route("/create_student", name="teachers_student_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update_student.html.twig")
     * @Acl(
     *      id="oro_user_user_create",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="CREATE"
     * )
     */

    public function createStudentAction()
    {
        /** @var User $user */
        $user = $this->get('oro_user.manager')->createUser();
        $user->setRoles([
            $this->getRoleHelper()->getStudentRole()
        ]);

        return $this->update($user);
    }

    /**
     * Create user form
     *
     * @Route("/create_course_manager", name="teachers_course_manager_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update_course_manager.html.twig")
     * @Acl(
     *      id="oro_user_user_create",
     *      type="entity",
     *      class="OroUserBundle:User",
     *      permission="CREATE"
     * )
     */
    public function createCourseManagerAction()
    {
        /** @var User $user */
        $user = $this->get('oro_user.manager')->createUser();
        $user->setRoles([
            $this->getRoleHelper()->getCourseManagerRole()
        ]);

        return $this->update($user);
    }

    private function getRoleHelper(): ?RoleHelper
    {
        return $this->container->get('teachers_users.helper.role');
    }

    /**
     * @param User $entity
     * @return RedirectResponse|array
     */
    private function update(User $entity)
    {
        /** @var \Oro\Bundle\FormBundle\Model\UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        /** @var \Symfony\Component\Form\FormInterface $form */
        $form = $this->get('oro_user.form.user');
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('oro.contact.controller.contact.saved.message'),
            null,
            'teachers_users.form.handler.user'
        );
    }
}
