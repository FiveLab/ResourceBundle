<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddErrorPresentationFactoryPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceAssemblerPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceSerializerPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddSerializationContextCollectorPass;
use FiveLab\Bundle\ResourceBundle\DependencyInjection\ResourceExtension;
use FiveLab\Bundle\ResourceBundle\FiveLabResourceBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class FiveLabResourceBundleTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessBuild(): void
    {
        $bundle = new FiveLabResourceBundle();
        $container = new ContainerBuilder();
        $bundle->build($container);

        $expectedContainer = new ContainerBuilder();
        $expectedContainer->addCompilerPass(new AddResourceAssemblerPass());
        $expectedContainer->addCompilerPass(new AddResourceSerializerPass());
        $expectedContainer->addCompilerPass(new AddSerializationContextCollectorPass());
        $expectedContainer->addCompilerPass(new AddErrorPresentationFactoryPass());
        $expectedContainer->addCompilerPass(new SerializerPass(
            'fivelab.resource.serializer',
            'resource.serializer.normalizer',
            'resource.serializer.encoder'
        ));

        self::assertEquals($expectedContainer, $container);
    }

    /**
     * @test
     */
    public function shouldSuccessGetExtension(): void
    {
        $bundle = new FiveLabResourceBundle();
        $extension = $bundle->getContainerExtension();

        self::assertEquals(new ResourceExtension(), $extension);
    }
}
