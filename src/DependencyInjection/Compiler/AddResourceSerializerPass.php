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

namespace FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler;

use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for add resource serializer to container via tag.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddResourceSerializerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function process(ContainerBuilder $container): void
    {
        $symfonySerializerDefinition = $container->findDefinition('serializer');
        $symfonyNormalizers = $symfonySerializerDefinition->getArgument(0);
        $symfonyEncoders = $symfonySerializerDefinition->getArgument(1);

        $resolver = $container->findDefinition('fivelab.resource.serializer_resolver');
        $services = $container->findTaggedServiceIds('resource.serializer');

        foreach ($services as $serviceId => $tags) {
            try {
                $attributes = $tags[0];

                if (!\array_key_exists('supportable', $attributes)) {
                    throw new \RuntimeException('The resource serializer should define with supportable instance. Please add the "supportable" attribute to "resource.serializer".');
                }

                $resourceSerializerDefinition = $container->getDefinition($serviceId);
                $resourceSerializerClass = $container->getParameterBag()->resolveValue($resourceSerializerDefinition->getClass());

                if (!\is_a($resourceSerializerClass, ResourceSerializerInterface::class, true)) {
                    throw new \RuntimeException(\sprintf(
                        'The resource serializer should implement "%s".',
                        ResourceSerializerInterface::class
                    ));
                }

                // Configure serializer
                /** @var Reference $serializerReference */
                $serializerReference = $resourceSerializerDefinition->getArgument(0);
                $serializerDefinition = $container->getDefinition((string) $serializerReference);

                $serializerNormalizers = $serializerDefinition->getArgument(0);
                $serializerNormalizers = \array_merge($serializerNormalizers, $symfonyNormalizers);

                $serializerDefinition
                    ->replaceArgument(0, $serializerNormalizers)
                    ->replaceArgument(1, $symfonyEncoders)
                    ->addMethodCall('setEventDispatcher', [new Reference('event_dispatcher')]);

                // Configure resolver
                $supportableServiceId = $attributes['supportable'];

                $resolver->addMethodCall('add', [
                    new Reference($supportableServiceId),
                    new Reference($serviceId),
                ]);
            } catch (\Exception $e) {
                throw new \RuntimeException(\sprintf(
                    'Can\'t compile resource serializer with service id "%s".',
                    $serviceId
                ), 0, $e);
            }
        }
    }
}
