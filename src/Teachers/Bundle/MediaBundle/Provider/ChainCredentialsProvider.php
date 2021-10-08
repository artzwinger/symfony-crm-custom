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
use Aws\DoctrineCacheAdapter;
use Aws\Exception\CredentialsException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class ChainCredentialsProvider
{
    /**
     * @var array Credential Providers
     */
    protected $providers = [];

    /**
     * @var DoctrineCacheAdapter
     */
    protected $cache;

    /**
     * ChainCredentialsProvider constructor.
     * @param DoctrineCacheAdapter $cache
     */
    public function __construct(DoctrineCacheAdapter $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param array $providers
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @param $provider
     * @return $this
     */
    public function addProvider($provider)
    {
        $this->providers[] = CredentialProvider::cache($provider, $this->cache);
        return $this;
    }

    /**
     * Load ECS credentials
     *
     * @return PromiseInterface
     */
    public function getCredentialChain()
    {
        return CredentialProvider::memoize(
            call_user_func_array(
                [
                    CredentialProvider::class,
                    'chain'
                ],
                $this->providers
            )
        );
    }
}
