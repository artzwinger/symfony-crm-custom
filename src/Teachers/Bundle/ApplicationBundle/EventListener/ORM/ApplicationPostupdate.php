<?php

namespace Teachers\Bundle\ApplicationBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
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
    /**
     * @var Organization
     */
    private $defaultOrganization;
    /**
     * @var BusinessUnit
     */
    private $defaultBusinessUnit;

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

        $student = $this->getStudent($application);
        $contact = $this->getStudentContact($application);
        $account = $this->getStudentAccount($contact);

        $application->setStudent($student);
        $application->setStudentContact($contact);
        $application->setStudentAccount($account);

        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }

    /**
     * @throws Exception
     */
    protected function getStudent(Application $application): User
    {
        $firstName = $application->getFirstName();
        $lastName = $application->getLastName();
        $email = $application->getEmail();
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
        $student->setOrganization($this->getDefaultOrganization());
        $student->addOrganization($this->getDefaultOrganization());
        $student->addBusinessUnit($this->getDefaultBusinessUnit());

        $this->userManager->updateUser($student, true);
        $this->sendInviteMail($student, $password);

        if (!$student->getId()) {
            throw new Exception('Cannot create a user');
        }
        return $student;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    protected function getStudentContact(Application $application): Contact
    {
        $firstName = $application->getFirstName();
        $lastName = $application->getLastName();
        $email = $application->getEmail();
        $phone = $application->getPhone();
        $contact = new Contact();
        $contact->setFirstName($firstName);
        $contact->setLastName($lastName);
        $phone = new ContactPhone($phone);
        $phone->setPrimary(true);
        $contact->addPhone($phone);
        $email = new ContactEmail($email);
        $email->setPrimary(true);
        $contact->addEmail($email);
        $contact->setOrganization($this->getDefaultOrganization());
        $this->entityManager->persist($contact);
        $this->entityManager->flush($contact);
        if (!$contact->getId()) {
            throw new Exception('Cannot create a contact');
        }
        return $contact;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    protected function getStudentAccount(Contact $contact): Account
    {
        $account = new Account();
        $account->setName($contact->getLastName() . ' ' . $contact->getFirstName());
        $account->setDefaultContact($contact);
        $account->setOrganization($this->getDefaultOrganization());
        $this->entityManager->persist($account);
        $this->entityManager->flush($account);
        if (!$account->getId()) {
            throw new Exception('Cannot create an account');
        }
        return $account;
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

    /**
     * @return Organization
     */
    protected function getDefaultOrganization()
    {
        if (null === $this->defaultOrganization) {
            $repo = $this->entityManager->getRepository('OroOrganizationBundle:Organization');
            $organizations = $repo->createQueryBuilder('e')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
            $this->defaultOrganization = current($organizations);
        }

        return $this->defaultOrganization;
    }

    /**
     * @return BusinessUnit
     */
    protected function getDefaultBusinessUnit(): BusinessUnit
    {
        if (null === $this->defaultBusinessUnit) {
            $repo = $this->entityManager->getRepository('OroOrganizationBundle:BusinessUnit');
            $units = $repo->createQueryBuilder('e')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
            $this->defaultBusinessUnit = current($units);
        }

        return $this->defaultBusinessUnit;
    }
}
