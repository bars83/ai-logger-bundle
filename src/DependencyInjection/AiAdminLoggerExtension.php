<?php
/**
 * AiAdminLoggerExtension
 *
 * PHP Version 5
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */

namespace Ai\Bundle\AdminLoggerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages a bundle configuration
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */
class AiAdminLoggerExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   configs
     * @param ContainerBuilder $container container
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter($this->getAlias(), $config);
    }
}
