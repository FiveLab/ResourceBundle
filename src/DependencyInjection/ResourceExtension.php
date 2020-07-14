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
use Symfony\Component\HttpKernel\KernelEvents;
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
     */
    protected function loadInternal(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureSerializer($config['serializer'], $container);
        $this->configureExceptionListener($config['logging'], $config['listeners']['exception'], $container);
        $this->configureListeners($config['listeners'], $container);
        $this->configureErrorPresentationFactory($config['error_presentation_factory'], $container);
    }

    /**
     * Configure error presentation factories
     *
     * @param array            $factoryConfig
     * @param ContainerBuilder $container
     */
    private function configureErrorPresentationFactory(array $factoryConfig, ContainerBuilder $container): void
    {
        if ($this->isConfigEnabled($container, $factoryConfig['validation'])) {
            $container->getDefinition('fivelab.resource.error_presentation_factory.validation_failed')
                ->setAbstract(false)
                ->replaceArgument(0, $factoryConfig['validation']['message'])
                ->replaceArgument(1, $factoryConfig['validation']['reason']);
        }
    }

    /**
     * Configure listeners
     *
     * @param array            $listenersConfig
     * @param ContainerBuilder $container
     */
    private function configureListeners(array $listenersConfig, ContainerBuilder $container): void
    {
        $container->getDefinition('fivelab.resource.event_listener.exception')
            ->replaceArgument(3, $listenersConfig['exception']['debug_parameter']);

        if ($this->isConfigEnabled($container, $listenersConfig['validation'])) {
            if (!\interface_exists(ValidatorInterface::class)) {
                throw new \RuntimeException('The validation listener is enabled but the Symfony/Validator not installed. Please install Symfony/Validator package.');
            }
        } else {
            $container->removeDefinition('fivelab.resource.event_listener.validate_resource');
        }

        if ($this->isConfigEnabled($container, $listenersConfig['symfony_security'])) {
            if (!\interface_exists(AuthorizationCheckerInterface::class)) {
                throw new \RuntimeException('The security listener is enabled but the Symfony/Security not installed. Please install Symfony/Security package.');
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
     * @param array            $loggingConfig
     * @param array            $exceptionListenerConfig
     * @param ContainerBuilder $container
     */
    private function configureExceptionListener(array $loggingConfig, array $exceptionListenerConfig, ContainerBuilder $container): void
    {
        if ($this->isConfigEnabled($container, $loggingConfig)) {
            $definition = $container
                ->getDefinition('fivelab.resource.event_listener.exception_logging')
                ->replaceArgument(2, $loggingConfig['level'])
                ->setAbstract(false)
                ->addTag('monolog.logger', ['channel' => $loggingConfig['channel']]);
        } else {
            $definition = $container->getDefinition('fivelab.resource.event_listener.logging');
        }

        $container
            ->getDefinition('fivelab.resource.event_listener.exception')
            ->replaceArgument(3, $exceptionListenerConfig['debug_parameter']);

        $definition->addTag('kernel.event_listener', [
            'event'  => KernelEvents::EXCEPTION,
            'method' => 'onKernelException',
        ]);
    }

    /**
     * Configure serializer
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    private function configureSerializer(array $config, ContainerBuilder $container): void
    {
        if ($config['metadata_factory']) {
            $container->setAlias('fivelab.resource.serializer.metadata_factory', $config['metadata_factory']);
        }

        if ($config['name_converter']) {
            $container->setAlias('fivelab.resource.serializer.name_converter', $config['name_converter']);
        }

        if ($config['property_accessor']) {
            $container->setAlias('fivelab.resource.serializer.property_accessor', $config['property_accessor']);
        }

        if ($config['property_info']) {
            $container->setAlias('fivelab.resource.serializer.property_info', $config['property_info']);
        }

        if ($config['event_dispatcher']) {
            $container->setAlias('fivelab.resource.serializer.event_dispatcher', $config['event_dispatcher']);
        }

        $container->setParameter('fivelab.resource.serializer.serialize_null', $config['serialize_null']);
    }
}
