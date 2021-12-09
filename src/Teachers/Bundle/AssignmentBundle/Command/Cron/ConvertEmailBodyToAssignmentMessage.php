<?php

namespace Teachers\Bundle\AssignmentBundle\Command\Cron;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DOMDocument;
use DOMXPath;
use Exception;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\ImapBundle\Entity\ImapEmail;
use Oro\Bundle\UserBundle\Entity\User;
use Predis\ClientInterface;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessageThread;
use Teachers\Bundle\AssignmentBundle\Helper\Messages;
use Teachers\Bundle\UsersBundle\Helper\Role;

/**
 * The CLI command to convert emails to assignment messages
 */
class ConvertEmailBodyToAssignmentMessage extends Command implements CronCommandInterface
{
    /**
     * @var string
     */
    protected static $defaultName = 'oro:cron:convert-email-body-to-assignment-message';
    /**
     * @var FeatureChecker
     */
    protected $featureChecker;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var ClientInterface
     */
    private $redisClient;
    /**
     * @var Messages
     */
    private $messagesHelper;
    /**
     * @var Role
     */
    private $roleHelper;

    /**
     * @param FeatureChecker $featureChecker
     * @param EntityManager $entityManager
     * @param Role $roleHelper
     * @param Messages $messagesHelper
     * @param ClientInterface $redisClient
     */
    public function __construct(
        FeatureChecker  $featureChecker,
        EntityManager   $entityManager,
        Role $roleHelper,
        Messages $messagesHelper,
        ClientInterface $redisClient
    )
    {
        parent::__construct();

        $this->featureChecker = $featureChecker;
        $this->entityManager = $entityManager;
        $this->messagesHelper = $messagesHelper;
        $this->redisClient = $redisClient;
        $this->roleHelper = $roleHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition(): string
    {
        return '*/3 * * * *';
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->featureChecker->isResourceEnabled(self::getDefaultName(), 'cron_jobs');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->featureChecker->isFeatureEnabled('email')) {
            $output->writeln('The email feature is disabled. The command will not run.');
            return 0;
        }
        if ($this->redisClient) {
            $store = new RedisStore($this->redisClient);
        } else {
            $output->writeln('Redis unavailable.');
            $store = new SemaphoreStore();
        }
        $lockFactory = new LockFactory($store);
        $lock = $lockFactory->createLock('oro:cron:convert-email-body-to-assignment-message');
        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }
        $this->processEmails($output);
        $lock->release();
        return 0;
    }

    /**
     */
    protected function processEmails(OutputInterface $output)
    {
        $emailsImap = $this->entityManager->getRepository(ImapEmail::class)->findAll();
        $emailImapIdsToIgnore = $this->getEmailImapIdsToIgnore($emailsImap);
        foreach ($emailsImap as $emailImap) {
            if (array_key_exists($emailImap->getId(), $emailImapIdsToIgnore)) {
                $output->writeln('Ignore email ' . $emailImap->getId() . ': already processed');
                continue;
            }
            try {
                $this->processImapEmail($emailImap, $output);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    /**
     * @param ImapEmail $emailImap
     * @param OutputInterface $output
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    protected function processImapEmail(ImapEmail $emailImap, OutputInterface $output)
    {
        /** @var Email $email */
        $email = $emailImap->getEmail();
        $this->validateEmailIsConvertible($email);
        $assignment = $this->getAssignmentFromEmail($email);
        $thread = $this->getThreadFromEmail($email, $output);

        $assignmentMessage = new AssignmentMessage();
        $assignmentMessage->setAssignment($assignment);
        $assignmentMessage->setMessage($this->extractMessageTextFromEmail($email));
        $assignmentMessage->setOrganization($assignment->getOrganization());

        $sender = $this->getMessageSenderUser($email, $assignment);
        $recipient = $this->getMessageRecipientUser($email, $assignment, $thread);

        if (!$thread->getId()) {
            $thread->setSender($sender);
            $thread->setRecipient($recipient);
            $this->entityManager->persist($thread);
            $this->entityManager->flush($thread);
        }

        $assignmentMessage->setOwner($sender);
        $assignmentMessage->setThread($thread);
        $assignmentMessage->setRecipient($recipient);
        $assignmentMessage->setEmailImap($emailImap);
        $assignmentMessage->setStatus($this->messagesHelper->getMessageStatusPending());

        $this->entityManager->persist($assignmentMessage);
        $this->entityManager->flush($assignmentMessage);

        $this->messagesHelper->autoApproveIfAllowed($assignmentMessage);
        $this->messagesHelper->updateThreadLatestMessage($assignmentMessage);
    }

    private function getEmailImapIdsToIgnore(array $emailsImap): array
    {
        $emailImapIds = array_map(function (ImapEmail $emailImap) {
            return $emailImap->getId();
        }, $emailsImap);
        $ids = $this->entityManager->getRepository(AssignmentMessage::class)
            ->filterEmailImapIds($emailImapIds);
        return array_flip($ids);
    }

    /**
     * @throws Exception
     */
    private function validateEmailIsConvertible(Email $email)
    {
        if (!strstr($email->getSubject(), 'New Message In The Assignment')) {
            throw new Exception('Subject of email #' . $email->getId() . ' does not contain assignment message indicators');
        }
    }

    /**
     * Returns a sender user if it's a tutor or a student assigned to the assignment
     * Otherwise throws an exception
     * @throws Exception
     */
    private function getMessageSenderUser(Email $email, Assignment $assignment): User
    {
        $sender = $email->getFromEmailAddress()->getEmail();
        $teacher = $assignment->getTeacher();
        $student = $assignment->getStudent();
        if ($teacher->getEmail() === $sender) {
            return $teacher;
        }
        if ($student->getEmail() === $sender) {
            return $student;
        }
        throw new Exception('Cannot put message from email #' . $email->getId() . ' to assignment #' . $assignment->getId() . ': sender is not student or tutor');
    }

    /**
     * Returns a recipient user if it's a tutor or a student assigned to the assignment
     * Otherwise throws an exception
     * @throws Exception
     */
    private function getMessageRecipientUser(Email $email, Assignment $assignment, AssignmentMessageThread $thread): ?User
    {
        $senderEmail = $email->getFromEmailAddress()->getEmail();
        if ($thread->getId()) {
            if ($thread->isThreadRecipientCourseManager()) {
                return $senderEmail === $thread->getSender()->getEmail() ? null : $thread->getSender();
            }
            $threadSenderCourseManager =
                $this->roleHelper->hasUserOneOfRoleNames($thread->getSender(), [Role::ROLE_COURSE_MANAGER, Role::ROLE_ADMINISTRATOR]);
            if ($threadSenderCourseManager) {
                return $senderEmail === $thread->getRecipient()->getEmail() ? null : $thread->getRecipient();
            }
        }
        // in other cases (if a thread doesn't exist or exists between student and tutor)
        // the default logic is applicable
        $teacher = $assignment->getTeacher();
        $student = $assignment->getStudent();
        if ($teacher->getEmail() === $senderEmail) {
            return $student;
        }
        if ($student->getEmail() === $senderEmail) {
            return $teacher;
        }
        throw new Exception('Cannot put message from email #' . $email->getId() . ' to assignment #' . $assignment->getId() . ': not possible to guess a recipient');
    }

    /**
     * @throws Exception
     */
    private function extractMessageTextFromEmail(Email $email): string
    {
        $body = $email->getEmailBody();
        if (!$body) {
            throw new Exception('Body does not exist for email ' . $email->getId());
        }
        try {
            $content = $body->getBodyContent();
            $doc = new DOMDocument();
            $doc->loadHTML($content);
            $this->removeElementsByClassNames($doc, ['quote', 'attr', 'moz-cite-prefix']);
            $this->removeElementsByTagName($doc, 'blockquote');
            $content = $doc->saveHTML();
            return trim(Html2Text::convert($content));
        } catch (Html2TextException $e) {
            return $body->getTextBody();
        }
    }

    /**
     * @throws Exception
     */
    private function getAssignmentFromEmail(Email $email): Assignment
    {
        preg_match('/Assignment #([0-9]+)/', $email->getSubject(), $matches);
        if (count($matches) < 2) {
            throw new Exception('Subject of email #' . $email->getId() . ' contains assignment message indicator, but does not contain assignment ID');
        }
        $assignmentId = $matches[1];
        /** @var Assignment $assignment */
        $assignment = $this->entityManager->getRepository(Assignment::class)->find($assignmentId);
        if (!$assignment) {
            throw new Exception('Cannot find assignment #' . $assignmentId . ' for email #' . $email->getId());
        }
        return $assignment;
    }

    /**
     * @throws Exception
     */
    private function getThreadFromEmail(Email $email, OutputInterface $output): AssignmentMessageThread
    {
        preg_match('/Thread #([0-9]+)/', $email->getSubject(), $matches);
        if (count($matches) < 2) {
            $output->writeLn('Subject of email #' . $email->getId() . ' does not contain thread identifier, creating a new thread...');
            return new AssignmentMessageThread();
        }
        $threadId = $matches[1];
        /** @var AssignmentMessageThread|null $thread */
        $thread = $this->entityManager->getRepository(AssignmentMessageThread::class)->find($threadId);
        if (!$thread) {
            $output->writeLn('Cannot find thread #' . $threadId . ' for email #' . $email->getId() . ', creating a new thread...');
            return new AssignmentMessageThread();
        }
        return $thread;
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function removeElementsByClassNames(DOMDocument $doc, array $classNames)
    {
        $finder = new DomXPath($doc);
        $firstClassName = array_unshift($classNames);
        $expression = "contains(@class, '$firstClassName')";
        foreach ($classNames as $className) {
            $expression .= "or contains(@class, '$className')";
        }
        $elements = $finder->query("//*[$expression]");
        foreach ($elements as $element) {
            if ($element->parentNode) {
                $element->parentNode->removeChild($element);
            }
        }
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function removeElementsByTagName(DOMDocument $doc, string $tagName)
    {
        $tags = $doc->getElementsByTagName($tagName);
        while ($tags->length > 0) {
            $p = $tags->item(0);
            $p->parentNode->removeChild($p);
        }
    }
}
