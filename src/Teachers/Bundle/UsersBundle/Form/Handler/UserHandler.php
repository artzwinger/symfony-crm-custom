<?php

namespace Teachers\Bundle\UsersBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teachers\Bundle\UsersBundle\Entity\TeacherGroupToUser;

/**
 * @package Teachers\Bundle\UsersBundle\Form\Handler
 */
class UserHandler implements FormHandlerInterface
{
    public const INVITE_USER_TEMPLATE = 'invite_user';
    use RequestHandlerTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var UserManager
     */
    protected $manager;
    /**
     * @var ConfigManager
     */
    protected $userConfigManager;
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var TokenAccessorInterface
     */
    private $tokenAccessor;
    /**
     * @var EmailTemplateManager
     */
    private $emailTemplateManager;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserManager $manager
     * @param EntityManager $entityManager
     * @param TokenAccessorInterface $tokenAccessor
     * @param ConfigManager $userConfigManager
     * @param EmailTemplateManager $emailTemplateManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserManager $manager,
        EntityManager $entityManager,
        TokenAccessorInterface $tokenAccessor,
        ConfigManager $userConfigManager,
        EmailTemplateManager $emailTemplateManager
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->userConfigManager = $userConfigManager;
        $this->tokenAccessor = $tokenAccessor;
        $this->emailTemplateManager = $emailTemplateManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data, FormInterface $form, Request $request): bool
    {
        $event = new FormProcessEvent($form, $data);
        $this->eventDispatcher->dispatch(Events::BEFORE_FORM_DATA_SET, $event);

        if ($event->isFormProcessInterrupted()) {
            return false;
        }

        $form->setData($data);

        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $event = new FormProcessEvent($form, $data);
            $this->eventDispatcher->dispatch(Events::BEFORE_FORM_SUBMIT, $event);

            if ($event->isFormProcessInterrupted()) {
                return false;
            }

            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($data, $form, $request);
                return true;
            }
        }

        return false;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function onSuccess(User $user, FormInterface $form, Request $request)
    {
        if (null === $user->getAuthStatus()) {
            $this->manager->setAuthStatus($user, UserManager::STATUS_ACTIVE);
        }

        $plainPassword = '';
        if (!$user->getId()) {
            $plainPassword = $this->handleNewUser($user, $form);
        }

        $this->manager->updateUser($user);
        if ($plainPassword) {
            $this->sendInviteMail($user, $plainPassword);
        }
        $requestData = $form->getName()
            ? $request->request->get($form->getName(), [])
            : $request->request->all();
        $teacherGroupIds = $requestData['teacherGroups'];
        if ($teacherGroupIds) {
            $teacherGroupIds = explode(',', $teacherGroupIds);
            foreach ($teacherGroupIds as $teacherGroupId) {
                $this->assignTeacherGroupToUser($user, $teacherGroupId);
            }
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function assignTeacherGroupToUser(User $user, $teacherGroupId)
    {
        $record = new TeacherGroupToUser();
        $record->setUser($user);
        $record->setTeacherGroup((int)$teacherGroupId);
        $this->entityManager->persist($record);
        $this->entityManager->flush($record);
    }

    /**
     * @param User $user
     * @param FormInterface $form
     * @return string
     */
    protected function handleNewUser(User $user, FormInterface $form): string
    {
        $sendPasswordInEmail = $this->userConfigManager &&
            $this->userConfigManager->get('oro_user.send_password_in_invitation_email');
        if (!$sendPasswordInEmail && !$user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }
        if ($form->has('passwordGenerate') && $form->get('passwordGenerate')->getData()) {
            $user->setPlainPassword($this->manager->generatePassword(10));
        }
        $user->setOrganization($this->tokenAccessor->getOrganization());

        return $sendPasswordInEmail ? $user->getPlainPassword() : '';
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
        if (in_array(null, [$this->userConfigManager, $this->emailTemplateManager], true)) {
            throw new RuntimeException('Unable to send invitation email, unmet dependencies detected.');
        }
        $senderEmail = $this->userConfigManager->get('oro_notification.email_notification_sender_email');
        $senderName = $this->userConfigManager->get('oro_notification.email_notification_sender_name');

        $this->emailTemplateManager->sendTemplateEmail(
            From::emailAddress($senderEmail, $senderName),
            [$user],
            new EmailTemplateCriteria(self::INVITE_USER_TEMPLATE, User::class),
            ['user' => $user, 'password' => $plainPassword]
        );
    }
}
