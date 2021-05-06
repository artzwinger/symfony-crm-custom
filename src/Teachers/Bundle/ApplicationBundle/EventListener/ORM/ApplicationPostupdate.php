<?php

namespace Teachers\Bundle\ApplicationBundle\EventListener\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Teachers\Bundle\ApplicationBundle\Entity\Application;
use Teachers\Bundle\UsersBundle\Helper\Role;

class ApplicationPostupdate
{
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

    public function __construct(
        EntityManager $entityManager,
        UserManager $userManager,
        Role $roleHelper
    )
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->roleHelper = $roleHelper;
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
        $userLogin = $application->getUserLogin();
        $userPassword = $application->getUserPassword();
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
        $student->setUsername($userLogin);
        $student->setPlainPassword($userPassword);

        $this->userManager->updateUser($student, true);

        if (!$student->getId()) {
            throw new Exception('Cannot create a user');
        }
        $application->setStudent($student);
        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }
}
