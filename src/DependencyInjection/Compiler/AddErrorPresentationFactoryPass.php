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

use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for adding error presentation factories to container via tag.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddErrorPresentationFactoryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function process(ContainerBuilder $container): void
    {
        $factoryChain = $container->findDefinition('fivelab.resource.error_presentation_factory');
        $factories = $container->findTaggedServiceIds('resource.error_presentation');

        foreach ($factories as $serviceId => $tags) {
            try {
                $factoryDefinition = $container->getDefinition($serviceId);
                $factoryClass = $container->getParameterBag()->resolveValue($factoryDefinition->getClass());

                if (!\is_a($factoryClass, ErrorPresentationFactoryInterface::class, true)) {
                    throw new \RuntimeException(\sprintf(
                        'The error presentation factory should implement "%s".',
                        ErrorPresentationFactoryInterface::class
                    ));
                }

                $factoryChain->addMethodCall('add', [new Reference($serviceId)]);
            } catch (\Exception $e) {
                throw new \RuntimeException(\sprintf(
                    'Cannot compile error presentation factory with id "%s".',
                    $serviceId
                ), 0, $e);
            }
        }
    }
}
