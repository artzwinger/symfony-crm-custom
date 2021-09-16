<?php

namespace Teachers\Bundle\AssignmentBundle\Command\Cron;

use Oro\Bundle\EmailBundle\Command\Cron\EmailBodySyncCommand;
use Oro\Bundle\EmailBundle\Sync\EmailBodySynchronizer;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Predis\ClientInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * The CLI command to synchronize email body.
 */
class EmailBodySyncCommandDecorator extends EmailBodySyncCommand
{
    /**
     * Number of emails in batch
     */
    const BATCH_SIZE = 25;

    /**
     * The maximum execution time (in minutes)
     */
    const MAX_EXEC_TIME_IN_MIN = 15;

    /** @var string */
    protected static $defaultName = 'oro:cron:email-body-sync';

    /** @var FeatureChecker */
    protected $featureChecker;

    /** @var EmailBodySynchronizer */
    protected $synchronizer;

    /**
     * @var EmailBodySyncCommand
     */
    private $decorated;
    /**
     * @var ClientInterface
     */
    private $redisClient;

    /**
     * @param EmailBodySyncCommand $decorated
     * @param FeatureChecker $featureChecker
     * @param EmailBodySynchronizer $synchronizer
     * @param ClientInterface|null $redisClient
     */
    public function __construct(
        EmailBodySyncCommand $decorated,
        FeatureChecker $featureChecker,
        EmailBodySynchronizer $synchronizer,
        ClientInterface $redisClient = null
    )
    {
        $this->decorated = $decorated;
        $this->redisClient = $redisClient;
        parent::__construct($featureChecker, $synchronizer);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition(): string
    {
        return '*/3 * * * *';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->redisClient) {
            $output->writeln('Redis unavailable.');
            return $this->decorated->execute($input, $output);
        }

        $output->writeln('Overridden command start');

        $store = new RedisStore($this->redisClient);
        $lockFactory = new LockFactory($store);

        $lock = $lockFactory->createLock('oro:cron:email-body-sync');
        if (!$lock->acquire()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $result = $this->decorated->execute($input, $output);

        $lock->release();

        return $result;
    }
}
