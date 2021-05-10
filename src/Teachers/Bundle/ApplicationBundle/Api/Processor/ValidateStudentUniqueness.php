<?php

namespace Teachers\Bundle\ApplicationBundle\Api\Processor;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class ValidateStudentUniqueness implements ProcessorInterface
{
    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @param UserManager $userManager
     */
    public function __construct(
        UserManager $userManager
    )
    {
        $this->userManager = $userManager;
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
    }
}
