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
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $treeBuilder = new TreeBuilder('fivelab_resource');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->append($this->createLoggingNode())
                ->append($this->createListenersNode())
                ->append($this->createErrorPresentationNode())
                ->append($this->createSerializersNode())
            ->end();

        return $treeBuilder;
    }

    /**
     * Create serializers node
     *
     * @return ArrayNodeDefinition
     */
    private function createSerializersNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('serializers');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->createSerializerNode('web_api', 'Enable simple WEB API serializer.', true))
                ->append($this->createSerializerNode('vnd_error', 'Enable vnd.error serializer.'))
                ->append($this->createSerializerNode('hateoas', 'Enable HATEOAS serializer.'))
            ->end();

        return $node;
    }

    /**
     * Create serializer node
     *
     * @param string $name
     * @param string $description
     * @param bool   $enabled
     *
     * @return ArrayNodeDefinition
     */
    private function createSerializerNode(string $name, string $description, bool $enabled = false): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition($name);

        $node
            ->addDefaultsIfNotSet()
            ->info($description)
            ->children()
                ->booleanNode('enabled')
                    ->info('Enable serializer?')
                    ->defaultValue($enabled)
                ->end()

                ->arrayNode('options')
                    ->info('The options for serialization.')
                    ->defaultValue([])
                    ->prototype('scalar')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Create error presentation node
     *
     * @return ArrayNodeDefinition
     */
    private function createErrorPresentationNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('error_presentation_factory');

        $node
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
            ->end();

        return $node;
    }

    /**
     * Create listeners node
     *
     * @return ArrayNodeDefinition
     */
    private function createListenersNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('listeners');

        $node
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
                ->end();

        return $node;
    }

    /**
     * Create logging node
     *
     * @return ArrayNodeDefinition
     */
    private function createLoggingNode(): ArrayNodeDefinition
    {
        $node = new ArrayNodeDefinition('logging');

        $node
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
            ->end();

        return $node;
    }
}
