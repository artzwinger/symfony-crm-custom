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

namespace Teachers\Bundle\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\VarDumper\VarDumper;

class ChainCredentialsProviderPass implements CompilerPassInterface
{
    const TAG = 'aligent_s3.crendential_provider';
    const CHAIN_SERVICE_ID = 'aligent_s3.credentials_provider.chain';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::CHAIN_SERVICE_ID)) {
            return;
        }

        $chainDefinition = $container->getDefinition(self::CHAIN_SERVICE_ID);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);


        foreach ($taggedServices as $serviceId => $service) {
            $chainDefinition->addMethodCall('addProvider', [new Reference($serviceId)]);
        }
    }
}
