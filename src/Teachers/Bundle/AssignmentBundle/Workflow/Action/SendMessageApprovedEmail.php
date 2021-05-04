<?php

namespace Teachers\Bundle\AssignmentBundle\Workflow\Action;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\LocalizedTemplateProvider;
use Oro\Bundle\EmailBundle\Tools\EmailAddressHelper;
use Oro\Bundle\EmailBundle\Tools\EmailOriginHelper;
use Oro\Bundle\EmailBundle\Workflow\Action\SendEmailTemplate;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Component\ConfigExpression\ContextAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\UsersBundle\Helper\Role;

/**
 * Workflow action that sends emails based on passed templates
 */
class SendMessageApprovedEmail extends SendEmailTemplate
{
    /**
     * @var Role
     */
    private $roleHelper;

    public function __construct(
        ContextAccessor $contextAccessor,
        Processor $emailProcessor,
        EmailAddressHelper $emailAddressHelper,
        EntityNameResolver $entityNameResolver,
        ManagerRegistry $registry,
        ValidatorInterface $validator,
        LocalizedTemplateProvider $localizedTemplateProvider,
        EmailOriginHelper $emailOriginHelper,
        Role $roleHelper
    )
    {
        parent::__construct($contextAccessor, $emailProcessor, $emailAddressHelper, $entityNameResolver, $registry, $validator, $localizedTemplateProvider, $emailOriginHelper);
        $this->roleHelper = $roleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options): SendEmailTemplate
    {
        return parent::initialize($options);
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction($context): void
    {
        /** @var AssignmentMessage $assignmentMessage */
        $assignmentMessage = $this->contextAccessor->getValue($context, $this->options['entity']);
        $this->options['to'] = [];
        $ownerId = $assignmentMessage->getOwner()->getId();
        $student = $assignmentMessage->getAssignment()->getStudent();
        $teacher = $assignmentMessage->getAssignment()->getTeacher();
        // teacher sent the message
        if ($this->roleHelper->hasThisUserIdThisRole($ownerId, Role::ROLE_TEACHER)) {
            if ($student) {
                $this->options['recipients'] = [
                    $student
                ];
            }
        } else if ($this->roleHelper->hasThisUserIdThisRole($ownerId, Role::ROLE_STUDENT)) { // student sent the message
            $this->options['recipients'] = [
                $teacher
            ];
        } else { // course manager sent the message
            $this->options['recipients'] = [
                $teacher
            ];
            if ($student) {
                $this->options['recipients'][] = $student;
            }
        }
        parent::executeAction($context);
    }
}
