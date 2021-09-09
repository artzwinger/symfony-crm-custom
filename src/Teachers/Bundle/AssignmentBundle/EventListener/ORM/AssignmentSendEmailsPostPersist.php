<?php

namespace Teachers\Bundle\AssignmentBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Exception;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AssignmentSendEmailsPostPersist
{
    const ASSIGNMENT_TUTOR_GROUP_TEMPLATE = 'teachers_assignment_tutor_group';
    /**
     * @var bool $processed
     */
    private static $processed = false;
    /**
     * @var EmailTemplateManager
     */
    private $emailTemplateManager;
    /**
     * @var ConfigManager
     */
    private $configManager;

    public function __construct(
        EmailTemplateManager $emailTemplateManager,
        ConfigManager        $configManager
    )
    {
        $this->emailTemplateManager = $emailTemplateManager;
        $this->configManager = $configManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        /** @var Assignment $assignment */
        $assignment = $args->getObject();
        if (self::$processed || !$assignment instanceof Assignment) {
            return;
        }
        self::$processed = true;
        $senderEmail = $this->configManager->get('oro_notification.email_notification_sender_email');
        $senderName = $this->configManager->get('oro_notification.email_notification_sender_name');
        $this->emailTemplateManager->sendTemplateEmail(
            From::emailAddress($senderEmail, $senderName),
            $this->getAssignmentTeachers($assignment),
            new EmailTemplateCriteria(self::ASSIGNMENT_TUTOR_GROUP_TEMPLATE, 'Teachers\Bundle\AssignmentBundle\Entity\Assignment'),
            ['entity' => $assignment]
        );
    }

    protected function getAssignmentTeachers(Assignment $assignment): array
    {
        $teachers = [];
        foreach ($assignment->getTeacherGroups() as $teacherGroup) {
            foreach ($teacherGroup->getTeachers() as $teacher) {
                $teachers[$teacher->getId()] = $teacher;
            }
        }
        return $teachers;
    }
}
