<?php
/**
 * This service is based on the core Oro\Bundle\AttachmentBundle\Migration\FilteredAttachmentMigrationService
 * However it uses the extended S3 adapter provided by this bundle to instead copy the images to the new location
 * the intent is that this is run locally prior to deployment so that the migration provided by the core
 * can be skipped and after the deploy the images manually deleted.
 *
 * @category  Aligent
 * @package
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2019 Aligent Consulting.
 * @license
 * @link      http://www.aligent.com.au/
 */

namespace Teachers\Bundle\MediaBundle\Service;


use Teachers\Bundle\MediaBundle\Adapter\AwsS3;
use Knp\Bundle\GaufretteBundle\FilesystemMap;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Gaufrette\Filesystem;
use Oro\Bundle\LayoutBundle\Loader\ImageFilterLoader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class FilteredAttachmentMigrationService
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfiguration;

    /**
     * @var ImageFilterLoader
     */
    protected $filterLoader;

    /**
     * @var \Gaufrette\Filesystem
     */
    protected $fileSystem;

    /**
     * @param FilterConfiguration $filterConfiguration
     * @param ImageFilterLoader $filterLoader
     */
    public function __construct(
        FilterConfiguration $filterConfiguration,
        ImageFilterLoader $filterLoader
    ) {
        $this->filterConfiguration = $filterConfiguration;
        $this->filterLoader = $filterLoader;
    }

    /**
     * @param string $fromPrefix
     * @param string $toPrefix
     * @return array
     */
    public function migrate(AwsS3 $adapter, string $fromPrefix, string $toPrefix)
    {
        $filterPathMap = $this->getFilterPathMap();

        if (!$adapter->isDirectory($fromPrefix)) {
            return [];
        }

        $pathRegEx = '/' . str_replace('/', '\/', $fromPrefix) . '\/(\d+)\/([^\/]+)\/([^\/]+)/';

        $processedFiles = [];
        foreach ($adapter->getKeyIterator($fromPrefix) as $key) {
            $key = $adapter->computeKey($key['Key']);
            
            if (0 !== strpos($key, $fromPrefix) || $adapter->isDirectory($key)) {
                continue;
            }

            $matches = [];
            if (preg_match($pathRegEx, $key, $matches) !== 1) {
                continue;
            }

            $fileId = $matches[1];
            $filterName = $matches[2];
            $fileName = $matches[3];

            if (empty($filterPathMap[$filterName])) {
                continue;
            }

            $newFilePath = $toPrefix . '/' . $filterPathMap[$filterName] . '/' . $fileId . '/' . $fileName;
            if (!$adapter->exists($newFilePath)) {
                $adapter->copy($key, $newFilePath);
            }
            $processedFiles[$fileId] = true;
        }

        return array_keys($processedFiles);
    }

    /**
     * @return array
     */
    private function getFilterPathMap(): array
    {
        $filterMap = [];
        $this->filterLoader->forceLoad();
        foreach ($this->filterConfiguration->all() as $filterName => $config) {
            $filterMap[$filterName] = $filterName . '/' . md5(json_encode($config));
        }

        return $filterMap;
    }

}
