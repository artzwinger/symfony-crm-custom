<?php

namespace Teachers\Bundle\ApplicationBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use RuntimeException;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\UsersBundle\Helper\Role;

class ApplicationPostupdate
{
    public const INVITE_USER_TEMPLATE = 'invite_user';
    /**
     * @var bool $proceed
     */
    private static $proceed = false;
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var Role
     */
    private $roleHelper;
    /**
     * @var ConfigManager
     */
    private $configManager;
    /**
     * @var EmailTemplateManager
     */
    private $emailTemplateManager;

    public function __construct(
        EntityManager $entityManager,
        UserManager $userManager,
        Role $roleHelper,
        ConfigManager $configManager,
        EmailTemplateManager $emailTemplateManager
    )
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->roleHelper = $roleHelper;
        $this->configManager = $configManager;
        $this->emailTemplateManager = $emailTemplateManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Application $application */
        $application = $args->getObject();
        if (self::$proceed || !$application instanceof Application) {
            return;
        }
        self::$proceed = true;
        if ($application->getStudent()) {
            return;
        }
        $firstName = $application->getFirstName();
        $lastName = $application->getLastName();
        $email = $application->getEmail();
//        $phone = $application->getPhone();

        /** @var User $student */
        $student = $this->userManager->createUser();
        $student->setRoles([
            $this->roleHelper->getStudentRole()
        ]);
        $student->setFirstName($firstName);
        $student->setLastName($lastName);
        $student->setEmail($email);
        $student->setUsername($email);
        $password = $this->userManager->generatePassword(10);
        $student->setPlainPassword($password);

        $this->userManager->updateUser($student, true);
        $this->sendInviteMail($student, $password);

        if (!$student->getId()) {
            throw new Exception('Cannot create a user');
        }
        $application->setStudent($student);
        $this->entityManager->persist($application);
        $this->entityManager->flush();
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
        if (in_array(null, [$this->configManager, $this->emailTemplateManager], true)) {
            throw new RuntimeException('Unable to send invitation email, unmet dependencies detected.');
        }
        $senderEmail = $this->configManager->get('oro_notification.email_notification_sender_email');
        $senderName = $this->configManager->get('oro_notification.email_notification_sender_name');

        $this->emailTemplateManager->sendTemplateEmail(
            From::emailAddress($senderEmail, $senderName),
            [$user],
            new EmailTemplateCriteria(self::INVITE_USER_TEMPLATE, User::class),
            ['user' => $user, 'password' => $plainPassword]
        );
    }
}
