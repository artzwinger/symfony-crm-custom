<?php

namespace Teachers\Bundle\AssignmentBundle\Command\Cron;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use DOMDocument;
use DOMXPath;
use Exception;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
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
     * @var ActivityManager
     */
    private $activityManager;
    /**
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * @param FeatureChecker $featureChecker
     * @param EntityManager $entityManager
     * @param ActivityManager $activityManager
     * @param ClientInterface $redisClient
     */
    public function __construct(
        FeatureChecker  $featureChecker,
        EntityManager   $entityManager,
        ActivityManager $activityManager,
        ClientInterface $redisClient
    )
    {
        parent::__construct();

        $this->featureChecker = $featureChecker;
        $this->entityManager = $entityManager;
        $this->activityManager = $activityManager;
        $this->redisClient = $redisClient;
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
        $assignmentMessage->setMessage($this->extractMessageTextFromEmail($email));
        $assignmentMessage->setOrganization($assignment->getOrganization());

        $sender = $this->getMessageSenderUser($email, $assignment);
        $recipient = $this->getMessageRecipientUser($email, $assignment);

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

        $this->activityManager->addActivityTarget($assignmentMessage, $assignment);
        $this->entityManager->persist($assignmentMessage);
        $this->entityManager->flush($assignmentMessage);
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
    private function getMessageRecipientUser(Email $email, Assignment $assignment): User
    {
        $sender = $email->getFromEmailAddress()->getEmail();
        $teacher = $assignment->getTeacher();
        $student = $assignment->getStudent();
        if ($teacher->getEmail() === $sender) {
            return $student;
        }
        if ($student->getEmail() === $sender) {
            return $teacher;
        }
        throw new Exception('Cannot put message from email #' . $email->getId() . ' to assignment #' . $assignment->getId() . ': not possible to guess a recipient');
    }

    private function extractMessageTextFromEmail(Email $email): string
    {
        try {
            $body = $email->getEmailBody()->getBodyContent();
            $body = $this->removeBlockQuote($body);
            return trim(Html2Text::convert($body));
        } catch (Html2TextException $e) {
            return $email->getEmailBody()->getTextBody();
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

    private function removeBlockQuote(string $body)
    {
        $doc = new DOMDocument();
        $doc->loadHTML($body);

        $finder = new DomXPath($doc);
        $attrClassName = 'attr';
        $quoteClassName = 'quote';
        $attrs = $finder->query("//*[contains(@class, '$attrClassName')]");
        $quotes = $finder->query("//*[contains(@class, '$quoteClassName')]");
        $blockquotes = $doc->getElementsByTagName('blockquote');
        while ($blockquotes->length > 0) {
            $p = $blockquotes->item(0);
            $p->parentNode->removeChild($p);
        }
        foreach ($attrs as $attr) {
            if ($attr->parentNode) {
                $attr->parentNode->removeChild($attr);
            }
        }
        foreach ($quotes as $quote) {
            if ($quote->parentNode) {
                $quote->parentNode->removeChild($quote);
            }
        }
        return $doc->saveHTML();
    }
}
