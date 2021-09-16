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

/**
 * The CLI command to convert emails to assignment messages
 */
class ConvertEmailBodyToAssignmentMessage extends Command implements CronCommandInterface
{
    /**
     * @var string
     */
    protected static $defaultName = 'teachers:cron:convert-email-body-to-assignment-message';
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
        FeatureChecker $featureChecker,
        EntityManager $entityManager,
        ActivityManager $activityManager,
        ClientInterface $redisClient
    ) {
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
        return '*/5 * * * *';
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
        $lock = $lockFactory->createLock('teachers:cron:convert-email-body-to-assignment-message');
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
        $imapEmails = $this->entityManager->getRepository(ImapEmail::class)->findAll();
        foreach ($imapEmails as $imapEmail) {
            /** @var Email $email */
            $email = $imapEmail->getEmail();
            try {
                $this->processEmail($email);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    protected function processEmail(Email $email)
    {
        $subject = $email->getSubject();
        // find indicator in subject
        if (!strstr($subject, 'New Message In The Assignment')) {
            throw new Exception('Subject of email #' . $email->getId() . ' does not contain assignment message indicators');
        }
        // parse assignment id
        preg_match('/#([0-9]+)/', $subject, $matches);
        if (count($matches) < 2) {
            throw new Exception('Subject of email #' . $email->getId() . ' contains assignment message indicator, but does not contain assignment ID');
        }
        $assignmentId = $matches[1];
        /** @var Assignment $assignment */
        $assignment = $this->entityManager->getRepository(Assignment::class)->find($assignmentId);
        if (!$assignment) {
            throw new Exception('Cannot find assignment #' . $assignmentId . ' for email #' . $email->getId());
        }
        $body = $email->getEmailBody()->getBodyContent();

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

        $body = $doc->saveHTML();
        $assignmentMessage = new AssignmentMessage();
        try {
            $assignmentMessage->setMessage(trim(Html2Text::convert($body)));
        } catch (Html2TextException $e) {
            $assignmentMessage->setMessage($email->getEmailBody()->getTextBody());
        }

        $assignmentMessage->setOrganization($assignment->getOrganization());
        // guess who is sender and make sender a message owner
        $sender = $email->getFromEmailAddress()->getEmail();
        $teacher = $assignment->getTeacher();
        $student = $assignment->getStudent();
        if ($teacher->getEmail() === $sender) {
            $assignmentMessage->setOwner($teacher);
        } else if ($student->getEmail() === $sender) {
            $assignmentMessage->setOwner($student);
        } else {
            throw new Exception('Cannot put message from email #' . $email->getId() . ' to assignment #' . $assignmentId . ': sender is not student or tutor');
        }
        // save and attach activity target
        $this->activityManager->addActivityTarget($assignmentMessage, $assignment);
        $this->entityManager->persist($assignmentMessage);
        $this->entityManager->flush($assignmentMessage);
    }
}
