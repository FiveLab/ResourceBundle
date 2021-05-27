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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The extension for extend Symfony application with Resource library.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'fivelab_resource';
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureExceptionListener($container, $mergedConfig['logging']);
        $this->configureListeners($container, $mergedConfig['listeners']);
        $this->configureErrorPresentationFactory($container, $mergedConfig['error_presentation_factory']);

        if ($mergedConfig['serializers']['web_api']['enabled']) {
            $loader->load('serializers/web-api.xml');

            $container->getDefinition('fivelab.resource.serializer.web_api_json')
                ->replaceArgument(2, $mergedConfig['serializers']['web_api']['options']);
        }

        if ($mergedConfig['serializers']['vnd_error']['enabled']) {
            $loader->load('serializers/vnd-error.xml');

            $container->getDefinition('fivelab.resource.serializer.vnd_error_json')
                ->replaceArgument(2, $mergedConfig['serializers']['vnd_error']['options']);
        }

        if ($mergedConfig['serializers']['hateoas']['enabled']) {
            $loader->load('serializers/hateoas.xml');

            $container->getDefinition('fivelab.resource.serializer.hateoas_json')
                ->replaceArgument(2, $mergedConfig['serializers']['hateoas']['options']);
        }
    }

    /**
     * Configure error presentation factories
     *
     * @param ContainerBuilder     $container
     * @param array<string, mixed> $factoryConfig
     */
    private function configureErrorPresentationFactory(ContainerBuilder $container, array $factoryConfig): void
    {
        if ($this->isConfigEnabled($container, $factoryConfig['validation'])) {
            $container->getDefinition('fivelab.resource.error_presentation_factory.validation_failed')
                ->setAbstract(false)
                ->replaceArgument(0, $factoryConfig['validation']['message'])
                ->replaceArgument(1, $factoryConfig['validation']['reason']);
        } else {
            $container->removeDefinition('fivelab.resource.error_presentation_factory.validation_failed');
        }
    }

    /**
     * Configure listeners
     *
     * @param ContainerBuilder     $container
     * @param array<string, mixed> $listenersConfig
     */
    private function configureListeners(ContainerBuilder $container, array $listenersConfig): void
    {
        $container->getDefinition('fivelab.resource.event_listener.exception')
            ->replaceArgument(3, $listenersConfig['exception']['debug_parameter']);

        if ($this->isConfigEnabled($container, $listenersConfig['validation'])) {
            if (!\interface_exists(ValidatorInterface::class)) {
                throw new \RuntimeException('The validation listener is enabled but the symfony/validator not installed. Please install symfony/validator package.');
            }
        } else {
            $container->removeDefinition('fivelab.resource.event_listener.validate_resource');
        }

        if ($this->isConfigEnabled($container, $listenersConfig['symfony_security'])) {
            if (!\interface_exists(AuthorizationCheckerInterface::class)) {
                throw new \RuntimeException('The security listener is enabled but the symfony/security not installed. Please install symfony/security package.');
            }
        } else {
            $container->removeDefinition('fivelab.resource.serializer.event_listener.symfony_granted_relation');
            $container->removeDefinition('fivelab.resource.serializer.event_listener.symfony_granted_action');
        }

        if (!$this->isConfigEnabled($container, $listenersConfig['normalize_resource'])) {
            $container->removeDefinition('fivelab.resource.event_listener.normalize_resource');
        }
    }

    /**
     * Configure exception listener
     *
     * @param ContainerBuilder     $container
     * @param array<string, mixed> $loggingConfig
     */
    private function configureExceptionListener(ContainerBuilder $container, array $loggingConfig): void
    {
        if ($this->isConfigEnabled($container, $loggingConfig)) {
            $container
                ->getDefinition('fivelab.resource.event_listener.exception_logging')
                ->replaceArgument(2, $loggingConfig['level'])
                ->addTag('monolog.logger', ['channel' => $loggingConfig['channel']]);
        } else {
            $container->removeDefinition('fivelab.resource.event_listener.exception_logging');
        }
    }
}
