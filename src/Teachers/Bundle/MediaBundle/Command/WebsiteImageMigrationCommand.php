<?php
/**
 *
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Teachers\Bundle\MediaBundle\Command;

use Teachers\Bundle\MediaBundle\Adapter\AwsS3;
use Teachers\Bundle\MediaBundle\Service\FilteredAttachmentMigrationService;
use Gaufrette\Filesystem;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Aws\S3\S3Client;

class WebsiteImageMigrationCommand extends ContainerAwareCommand
{
    protected const FILTER_PREFIX = 'media/cache/attachment/filter';
    protected const RESIZE_PREFIX = 'media/cache/attachment/resize';

    protected static $defaultName = 'aligent:s3:migrate-website-images';

    /**
     * @var OutputInterface\
     */
    protected $output;

    /**
     * Configure Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Copies Website images to the new location required by Oro 3.1.12')
            ->addArgument(
                'bucket', InputArgument::REQUIRED, 'Which S3 Bucket do you want to migrate?'
            )
            ->addArgument('key', InputArgument::REQUIRED, 'Amazon API Key')
            ->addArgument('secret', InputArgument::REQUIRED, 'Amazon API Secret')
            ->addArgument('region', InputArgument::REQUIRED, 'Amazon Bucket Region');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $s3client = new S3Client([
            'credentials' => [
                'key'    => $input->getArgument('key'),
                'secret' => $input->getArgument('secret'),
            ],
            'version'     => 'latest',
            'region'      => $input->getArgument('region'),
        ]);

        $adapter = new AwsS3($s3client, $input->getArgument('bucket'));

        /** @var FilteredAttachmentMigrationService $migrationService */
        $migrationService = $this->getContainer()->get('aligent_s3.attachment_migration_service');

        $this->migrateFilteredImages($migrationService, $adapter);
        $this->migrateResizeImages($migrationService, $adapter);

        $output->writeln('Images have been successfully copied to the new destination.');
    }

    /**
     * Migrate Website scoped filtered images
     * @param FilteredAttachmentMigrationService $migrationService
     * @param AwsS3 $adapter
     */
    protected function migrateFilteredImages($migrationService, $adapter)
    {
        $websiteRepo = $this->getContainer()->get('doctrine')->getRepository(Website::class);
        $websites = $websiteRepo->getAllWebsites();
        $websiteManager = $this->getContainer()->get('oro_website.manager');
        $configManager = $this->getContainer()->get('oro_config.website');

        $websiteIds = [];
        foreach ($websites as $website) {
            $this->output->writeln('Migrating images for website: ' . $website->getName());
            $websiteId = $website->getId();
            $websiteIds[] = $websiteId;
            $websiteManager->setCurrentWebsite($website);
            $configManager->setScopeId($websiteId);

            $migrationService->migrate($adapter, self::FILTER_PREFIX . '/' . $websiteId, self::FILTER_PREFIX);
        }
    }

    /**
     * Migrate Resized Images
     * @param FilteredAttachmentMigrationService $migrationService
     * @param AwsS3 $adapter
     * @return mixed
     */
    protected function migrateResizeImages($migrationService, $adapter)
    {
        return $migrationService->migrate($adapter, self::RESIZE_PREFIX, self::RESIZE_PREFIX);
    }
}
