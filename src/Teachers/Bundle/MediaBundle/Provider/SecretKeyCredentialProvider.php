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

namespace Teachers\Bundle\MediaBundle\Provider;

use Aws\Credentials\CredentialProvider;
use Aws\Credentials\Credentials;
use GuzzleHttp\Promise;

class SecretKeyCredentialProvider
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $secret;

    /**
     * SecretKeyCredentialProvider constructor.
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @return RejectedPromise|Promise
     */
    public function __invoke()
    {
        if ($this->key && $this->secret) {
            return Promise\promise_for(
                new Credentials($this->key, $this->secret)
            );
        }

        $msg = 'Missing amazon_s3.key or amazon_s3.secret';
        return new RejectedPromise(new CredentialsException($msg));
    }
}
