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

namespace Teachers\Bundle\MediaBundle\Adapter;

use Gaufrette\Adapter\AwsS3 as BaseAdapter;
class AwsS3 extends BaseAdapter
{

    /**
     * Returns a key iterator
     * @return \Iterator
     */
    public function getKeyIterator($prefix)
    {
        $this->ensureBucketExists();

        $options = ['Bucket' => $this->bucket];
        if ((string) $prefix != '') {
            $options['Prefix'] = $this->computePath($prefix);
        } elseif (!empty($this->options['directory'])) {
            $options['Prefix'] = $this->options['directory'];
        }

        $iter = $this->service->getIterator('ListObjects', $options);

        return $iter;
    }

    protected function computePath($key)
    {
        $key = ltrim($key, '/');
        return parent::computePath($key);
    }

    /**
     * Computes the key from the specified path.
     *
     * @param string $path
     *
     * return string
     */
    public function computeKey($path)
    {
        return parent::computeKey($path);
    }

    /*
     * Copies the file at $originalFilePath to $newFilePath
     */
    public function copy($originalFilePath, $newFilePath)
    {
        $this->service->copy($this->bucket, $originalFilePath, $this->bucket, $newFilePath);
    }
}
