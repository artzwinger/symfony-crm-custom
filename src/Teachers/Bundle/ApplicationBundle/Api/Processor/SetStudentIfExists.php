<?php

namespace Teachers\Bundle\ApplicationBundle\Api\Processor;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class SetStudentIfExists implements ProcessorInterface
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @param UserManager $userManager
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        UserManager $userManager,
        DoctrineHelper $doctrineHelper
    )
    {
        $this->userManager = $userManager;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function process(ContextInterface $context)
    {
        /** @var Application $application */
        $application = $context->getResult();
        $email = $application->getEmail();
        /** @var User $user */
        $user = $this->userManager->findUserByEmail($email);
        if ($user and $user->getId()) {
            $application->setStudent($user);
        }
        /** @var ContactRepository $contactRepository */
        $contactRepository = $this->doctrineHelper->getEntityRepository(Contact::class);
        /** @var Contact|null $contact */
        $contact = $contactRepository->findOneBy(['email' => $email]);
        if ($contact and $contact->getId()) {
            $application->setStudentContact($contact);
            $accountRepository = $this->doctrineHelper->getEntityRepository(Account::class);
            /** @var Account|null $account */
            $account = $accountRepository->findOneBy(['defaultContact' => $contact]);
            if ($account and $account->getId()) {
                $application->setStudentAccount($account);
            }
        }
    }
}
