<?php
/**
 * Configuration
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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @category Class
 * @package  Ai\Bundle\AdminLoggerBundle
 * @author   Ruslan Muriev <ruslana.net@gmail.com>
 * @license  https://github.com/ruslana-net/ai-logger-bundle/LICENSE MIT License
 * @link     https://github.com/ruslana-net/ai-logger-bundle
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ai_admin_logger');

        $rootNode
            ->children()
                ->scalarNode('type')
                    ->isRequired()->end()
                ->arrayNode('mapping')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('class', false)
                    ->prototype('array')->children()
                        ->scalarNode('class')
                            ->isRequired()->end()
                        ->scalarNode('title_field')
                            ->defaultValue('title')
                            ->isRequired()
                        ->end()
                        ->arrayNode('category')
                            ->children()
                                ->scalarNode('field')
                                    ->defaultValue('category')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('title_field')
                                    ->defaultValue('title')
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('fields')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
