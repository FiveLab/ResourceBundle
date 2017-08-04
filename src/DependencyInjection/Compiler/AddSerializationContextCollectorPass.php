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

use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for adding serialization context collector to container via tag.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddSerializationContextCollectorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function process(ContainerBuilder $container): void
    {
        $collector = $container->findDefinition('fivelab.resource.serializer.context_collector');
        $services = $container->findTaggedServiceIds('resource.serializer.collector');

        foreach ($services as $serviceId => $tags) {
            try {
                $collectorDefinition = $container->getDefinition($serviceId);
                $collectorClass = $container->getParameterBag()->resolveValue($collectorDefinition->getClass());

                if (!is_a($collectorClass, SerializationContextCollectorInterface::class, true)) {
                    throw new \RuntimeException(sprintf(
                        'The serialization context collector should implement "%s".',
                        SerializationContextCollectorInterface::class
                    ));
                }

                $collector->addMethodCall('add', [
                    new Reference($serviceId),
                ]);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Can\'t compile resource serialization collector with service id "%s".',
                    $serviceId
                ), 0, $e);
            }
        }
    }
}
