<?php

namespace Teachers\Bundle\UsersBundle\Controller;

use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Teachers\Bundle\UsersBundle\Helper\Role as RoleHelper;

class UserController extends AbstractController
{
    public const INVITE_USER_TEMPLATE = 'invite_user';
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

        return $this->update($user, $this->get('teachers_users.form.teacher'));
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

        return $this->update($user, $this->get('oro_user.form.user'));
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

        return $this->update($user, $this->get('oro_user.form.user'));
    }

    private function getRoleHelper(): ?RoleHelper
    {
        return $this->container->get('teachers_users.helper.role');
    }

    /**
     * @param User $entity
     * @param FormInterface $form
     * @return RedirectResponse|array
     */
    private function update(User $entity, FormInterface $form)
    {
        /** @var UpdateHandlerFacade $handler */
        $handler = $this->get('oro_form.update_handler');
        $result = $handler->update(
            $entity,
            $form,
            $this->get('translator')->trans('oro.contact.controller.contact.saved.message'),
            null,
            'teachers_users.form.handler.user'
        );
        if ($entity->getId()) {
            $this->sendInviteMail($entity, $entity->getPlainPassword());
        }
        return $result;
    }

    /**
     * Send invite email to new user
     *
     * @param User $user
     * @param string $plainPassword
     *
     * @throws RuntimeException
     */
    protected function sendInviteMail(User $user, string $plainPassword)
    {
        $configManager = $this->get('oro_config.manager');
        $emailTemplateManager = $this->get('oro_email.manager.template_email');
        if (in_array(null, [$configManager, $emailTemplateManager], true)) {
            throw new RuntimeException('Unable to send invitation email, unmet dependencies detected.');
        }
        $senderEmail = $configManager->get('oro_notification.email_notification_sender_email');
        $senderName = $configManager->get('oro_notification.email_notification_sender_name');

        $emailTemplateManager->sendTemplateEmail(
            From::emailAddress($senderEmail, $senderName),
            [$user],
            new EmailTemplateCriteria(self::INVITE_USER_TEMPLATE, User::class),
            ['user' => $user, 'password' => $plainPassword]
        );
    }
}
