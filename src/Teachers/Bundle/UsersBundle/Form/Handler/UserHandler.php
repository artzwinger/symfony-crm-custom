<?php

namespace Teachers\Bundle\UsersBundle\Form\Handler;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @package Teachers\Bundle\UsersBundle\Form\Handler
 */
class UserHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
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
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param UserManager $manager
     * @param \Oro\Bundle\ConfigBundle\Config\ConfigManager|null $userConfigManager
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserManager $manager,
        ConfigManager $userConfigManager = null
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->manager = $manager;
        $this->userConfigManager = $userConfigManager;
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
                $this->onSuccess($data, $form);
                return true;
            }
        }

        return false;
    }

    protected function onSuccess(User $user, FormInterface $form)
    {
        if (null === $user->getAuthStatus()) {
            $this->manager->setAuthStatus($user, UserManager::STATUS_ACTIVE);
        }

        if (!$user->getId()) {
            $this->handleNewUser($user, $form);
        }

        $this->manager->updateUser($user);
    }

    /**
     * @param User $user
     */
    protected function handleNewUser(User $user, FormInterface $form): void
    {
        $sendPasswordInEmail = $this->userConfigManager &&
            $this->userConfigManager->get('oro_user.send_password_in_invitation_email');

        if (!$sendPasswordInEmail && !$user->getConfirmationToken()) {
            $user->setConfirmationToken($user->generateToken());
        }

        if ($form->has('passwordGenerate') && $form->get('passwordGenerate')->getData()) {
            $user->setPlainPassword($this->manager->generatePassword(10));
        }
    }
}
