<?php

namespace Teachers\Bundle\UsersBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\UsersBundle\Helper\Role as RoleHelper;

class UserController extends AbstractController
{
    /**
     * Create user form
     *
     * @Route("/create_teacher", name="teachers_teacher_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update.html.twig")
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

        return $this->update($user, 'teachers_teacher_create');
    }

    /**
     * Create user form
     *
     * @Route("/create_student", name="teachers_student_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update.html.twig")
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

        return $this->update($user, 'teachers_student_create');
    }

    /**
     * Create user form
     *
     * @Route("/create_course_manager", name="teachers_course_manager_create", options={"expose"=true})
     * @Template("TeachersUsersBundle:User:update.html.twig")
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

        return $this->update($user, 'teachers_course_manager_create');
    }

    private function getRoleHelper(): ?RoleHelper
    {
        return $this->container->get('teachers_users.helper.role');
    }

    /**
     * @param User $entity
     * @param $action
     * @return RedirectResponse|array
     */
    private function update(User $entity, $action)
    {
        /** @var \Oro\Bundle\FormBundle\Model\UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $form = $this->getForm($action);
        return $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('oro.contact.controller.contact.saved.message')
        );
    }

    private function getForm($action): FormInterface
    {
        /** @var \Symfony\Component\Form\FormFactory $factory */
        $factory = $this->get('form.factory');
        $builder = $factory->createNamedBuilder('teachers_user_form', 'Oro\Bundle\UserBundle\Form\Type\UserType');
        $builder->setAction($action);

        return $builder->getForm();
    }
}
