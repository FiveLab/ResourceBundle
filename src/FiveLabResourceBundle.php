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

namespace FiveLab\Bundle\ResourceBundle;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddErrorPresentationFactoryPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceAssemblerPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceSerializerPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddSerializationContextCollectorPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\ResourceExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;

/**
 * The bundle for integrate the Resource library with Symfony application.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class FiveLabResourceBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddResourceAssemblerPass());
        $container->addCompilerPass(new AddResourceSerializerPass());
        $container->addCompilerPass(new AddSerializationContextCollectorPass());
        $container->addCompilerPass(new AddErrorPresentationFactoryPass());
        $container->addCompilerPass(new SerializerPass(
            'fivelab.resource.serializer',
            'resource.serializer.normalizer',
            'resource.serializer.encoder'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ResourceExtension
    {
        if (!$this->extension) {
            $this->extension = new ResourceExtension();
        }

        return $this->extension;
    }
}
