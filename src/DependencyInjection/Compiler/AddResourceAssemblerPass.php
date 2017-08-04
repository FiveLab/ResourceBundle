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

use FiveLab\Component\Resource\Assembler\ResourceAssemblerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for adding resource assembler to container via tag.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddResourceAssemblerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function process(ContainerBuilder $container): void
    {
        $resolver = $container->findDefinition('fivelab.resource.assembler_resolver');
        $services = $container->findTaggedServiceIds('resource.assembler');

        foreach ($services as $serviceId => $tags) {
            try {
                $attributes = $tags[0];

                if (!array_key_exists('supportable', $attributes)) {
                    throw new \RuntimeException('The resource assembler should define with supportable instance. Please add the "supportable" attribute to "resource.assembler".');
                }

                $assemblerDefinition = $container->getDefinition($serviceId);
                $assemblerClass = $container->getParameterBag()->resolveValue($assemblerDefinition->getClass());

                if (!is_a($assemblerClass, ResourceAssemblerInterface::class, true)) {
                    throw new \RuntimeException(sprintf(
                        'The resource assembler should implement "%s".',
                        ResourceAssemblerInterface::class
                    ));
                }

                $supportableServiceId = $attributes['supportable'];

                $resolver->addMethodCall('add', [
                    new Reference($supportableServiceId),
                    new Reference($serviceId),
                ]);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf(
                    'Can\'t compile resource assembler with service id "%s".',
                    $serviceId
                ), 0, $e);
            }
        }
    }
}
