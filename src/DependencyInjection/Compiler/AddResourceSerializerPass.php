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
        $resolver = $container->findDefinition('fivelab.resource.serializer_resolver');
        $services = $container->findTaggedServiceIds('resource.serializer');

        foreach ($services as $serviceId => $tags) {
            try {
                $attributes = $tags[0];

                if (!array_key_exists('supportable', $attributes)) {
                    throw new \RuntimeException('The resource serializer should define with supportable instance. Please add the "supportable" attribute to "resource.assembler".');
                }

                $serializerDefinition = $container->getDefinition($serviceId);
                $serializerClass = $container->getParameterBag()->resolveValue($serializerDefinition->getClass());

                if (!is_a($serializerClass, ResourceSerializerInterface::class, true)) {
                    throw new \RuntimeException(sprintf(
                        'The resource serializer should implement "%s".',
                        ResourceSerializerInterface::class
                    ));
                }

                $supportableServiceId = $attributes['supportable'];

                $resolver->addMethodCall('add', [
                    new Reference($supportableServiceId),
                    new Reference($serviceId),
                ]);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Can\'t compile resource serializer with service id "%s".',
                    $serviceId
                ), 0, $e);
            }
        }
    }
}
