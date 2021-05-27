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

namespace FiveLab\Bundle\ResourceBundle\Tests\DependencyInjection;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\ResourceExtension;
use FiveLab\Bundle\ResourceBundle\EventListener\ExceptionListener;
use FiveLab\Bundle\ResourceBundle\EventListener\LoggingExceptionListener;
use FiveLab\Bundle\ResourceBundle\EventListener\PresentationViewListener;
use FiveLab\Bundle\ResourceBundle\EventListener\ValidateResourceListener;
use FiveLab\Bundle\ResourceBundle\ParamConverter\ResourceParamConverter;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactory;
use FiveLab\Bundle\ResourceBundle\Resource\Error\Factory\ValidationFailedErrorPresentationFactory;
use FiveLab\Bundle\ResourceBundle\Resource\EventListener\GenerateSymfonyRouteHrefListener;
use FiveLab\Bundle\ResourceBundle\Resource\EventListener\SymfonyGrantedActionListener;
use FiveLab\Bundle\ResourceBundle\Resource\EventListener\SymfonyGrantedRelationListener;
use FiveLab\Component\Resource\Assembler\Resolver\ResourceAssemblerResolver;
use FiveLab\Component\Resource\Resource\EventListener\NormalizeNormalizableResourcesListener;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollector;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolver;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        return [
            new ResourceExtension(),
        ];
    }

    /**
     * @test
     */
    public function shouldSuccessBuildWithoutConfig(): void
    {
        $this->load([]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer_resolver', ResourceSerializerResolver::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.context_collector', SerializationContextCollector::class);
        $this->assertContainerBuilderHasService('fivelab.resource.assembler_resolver', ResourceAssemblerResolver::class);
        $this->assertContainerBuilderHasService('fivelab.resource.error_presentation_factory', ErrorPresentationFactory::class);

        // Check param converter
        $this->assertContainerBuilderHasService('fivelab.resource.param_converter.resource', ResourceParamConverter::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.param_converter.resource', 'request.param_converter');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.param_converter.resource', 0, new Reference('fivelab.resource.serializer_resolver'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.param_converter.resource', 1, new Reference('fivelab.resource.serializer.context_collector'));

        // Check validation presentation factory
        $this->assertContainerBuilderHasService('fivelab.resource.error_presentation_factory.validation_failed', ValidationFailedErrorPresentationFactory::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.error_presentation_factory.validation_failed', 0, 'Validation failed.');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.error_presentation_factory.validation_failed', 1, 'ValidationFailed');
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.error_presentation_factory.validation_failed', 'resource.error_presentation');


        // Check event listener system
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.event_listener.generate_symfony_routes', GenerateSymfonyRouteHrefListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.event_listener.generate_symfony_routes', 0, new Reference('router'));
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.event_listener.generate_symfony_routes', 'kernel.event_listener', [
            'event'    => 'resource.serializer.before_normalization',
            'method'   => 'onBeforeNormalization',
            'priority' => -128,
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.event_listener.symfony_granted_relation', SymfonyGrantedRelationListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.event_listener.symfony_granted_relation', 0, new Reference('security.authorization_checker'));
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.event_listener.symfony_granted_relation', 'kernel.event_listener', [
            'event'    => 'resource.serializer.before_normalization',
            'method'   => 'onBeforeNormalization',
            'priority' => 128,
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.event_listener.symfony_granted_action', SymfonyGrantedActionListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.event_listener.symfony_granted_action', 0, new Reference('security.authorization_checker'));
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.event_listener.symfony_granted_action', 'kernel.event_listener', [
            'event'    => 'resource.serializer.before_normalization',
            'method'   => 'onBeforeNormalization',
            'priority' => 128,
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.event_listener.presentation', PresentationViewListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.presentation', 0, new Reference('fivelab.resource.serializer_resolver'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.presentation', 1, new Reference('fivelab.resource.serializer.context_collector'));
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.event_listener.presentation', 'kernel.event_listener', [
            'event'  => 'kernel.view',
            'method' => 'onKernelView',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.event_listener.validate_resource', ValidateResourceListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.validate_resource', 0, new Reference('validator'));
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.event_listener.validate_resource', 'kernel.event_listener', [
            'event'  => 'kernel.controller_arguments',
            'method' => 'onKernelControllerArguments',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.event_listener.exception', ExceptionListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception', 0, new Reference('fivelab.resource.error_presentation_factory'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception', 1, new Reference('fivelab.resource.serializer_resolver'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception', 2, new Reference('fivelab.resource.serializer.context_collector'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception', 3, '_debug');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception', 4, '%kernel.debug%');
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.event_listener.exception', 'kernel.event_listener', [
            'event'  => 'kernel.exception',
            'method' => 'onKernelException',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.event_listener.exception_logging', LoggingExceptionListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception_logging', 0, new Reference('fivelab.resource.event_listener.exception_logging.inner'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception_logging', 1, new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.event_listener.exception_logging', 2, 'error');

        $this->assertContainerBuilderHasService('fivelab.resource.event_listener.normalize_resource', NormalizeNormalizableResourcesListener::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.event_listener.normalize_resource', 'kernel.event_listener', [
            'event'  => 'resource.serializer.after_denormalization',
            'method' => 'onAfterDenormalize',
        ]);
    }
}
