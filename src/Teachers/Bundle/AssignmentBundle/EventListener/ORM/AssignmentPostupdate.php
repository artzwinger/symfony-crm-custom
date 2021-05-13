<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Model\ExtendContactEmail;
use Oro\Bundle\UserBundle\Entity\User;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AssignmentPostupdate
{
    /**
     * @var bool $proceed
     */
    private static $proceed = false;
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (self::$proceed || !$assignment instanceof Assignment) {
            return;
        }
        self::$proceed = true;
        if (!$assignment->getTeacher() || $assignment->getTeacherContact()) {
            return;
        }

        $contact = $this->getTeacherContact($assignment->getTeacher());
        $account = $this->getTeacherAccount($contact);

        $assignment->setTeacherContact($contact);
        $assignment->setStudentAccount($account);

        $this->entityManager->persist($assignment);
        $this->entityManager->flush();
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    protected function getTeacherContact(User $teacher): Contact
    {
        /** @var ContactRepository $contactRepository */
        $contactRepository = $this->entityManager->getRepository(Contact::class);
        $contact = $contactRepository->findOneBy(['email' => $teacher->getEmail()]);
        if ($contact) {
            return $contact;
        }
        $firstName = $teacher->getFirstName();
        $lastName = $teacher->getLastName();
        $email = $teacher->getEmail();
        $contact = new Contact();
        $contact->setFirstName($firstName);
        $contact->setLastName($lastName);
        $email = new ContactEmail($email);
        $email->setPrimary(true);
        $contact->setEmail($email);
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
    protected function getTeacherAccount(Contact $contact): Account
    {
        $repo = $this->entityManager->getRepository(Account::class);
        $account = $repo->findOneBy(['defaultContact' => $contact]);
        if ($account) {
            return $account;
        }
        $account = new Account();
        $account->setName($contact->getLastName() . ' ' . $contact->getFirstName());
        $account->setDefaultContact($contact);
        $this->entityManager->persist($account);
        $this->entityManager->flush($account);
        if (!$account->getId()) {
            throw new Exception('Cannot create an account');
        }
        return $account;
    }
}
