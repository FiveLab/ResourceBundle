<?php

declare(strict_types = 1);

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\DependencyInjection;

use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * The configuration for Resource library.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fivelab_resource');

        $rootNode
            ->children()
                ->arrayNode('serializer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('metadata_factory')
                            ->info('The service id of class metadata factory.')
                            ->defaultValue('serializer.mapping.class_metadata_factory')
                        ->end()

                        ->scalarNode('name_converter')
                            ->info('The service id of name converter.')
                            ->defaultNull()
                        ->end()

                        ->scalarNode('property_accessor')
                            ->info('The service id of property accessor.')
                            ->defaultValue('property_accessor')
                        ->end()

                        ->scalarNode('property_info')
                            ->info('The service id of property info reader.')
                            ->defaultValue('property_info')
                        ->end()

                        ->scalarNode('event_dispatcher')
                            ->info('The service id of event dispatcher.')
                            ->defaultValue('event_dispatcher')
                        ->end()

                        ->scalarNode('serialize_null')
                            ->info('Serialize nullable attributes.')
                            ->defaultValue(true)
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('logging')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->info('If logging enabled?')
                            ->defaultValue(true)
                        ->end()

                        ->scalarNode('channel')
                            ->info('The channel for Monolog.')
                            ->defaultValue('api')
                        ->end()

                        ->scalarNode('level')
                            ->info('The level for logging.')
                            ->defaultValue(LogLevel::ERROR)
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('exception')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('debug_parameter')
                                    ->info('The name of debug parameter (Work only in kernel.debug mode).')
                                    ->defaultValue('_debug')
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('validation')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Enable validation listener. The Symfony/Validator package must be installed.')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('symfony_security')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Enable Symfony Grant Relation listener. The Symfony/Security package must be installed.')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('normalize_resource')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Enable normalize normalizable resources listener.')
                                    ->defaultTrue()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('error_presentation_factory')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('validation')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->info('Enable validation error presentation factory.')
                                    ->defaultTrue()
                                ->end()

                                ->scalarNode('message')
                                    ->info('The message for create error.')
                                    ->defaultValue('Validation failed.')
                                ->end()

                                ->scalarNode('reason')
                                    ->info('The reason for create error.')
                                    ->defaultValue('ValidationFailed')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
