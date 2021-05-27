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

namespace FiveLab\Bundle\ResourceBundle\Tests\DependencyInjection\Compiler;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddErrorPresentationFactoryPass;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactory;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddErrorPresentationFactoryPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setDefinition('fivelab.resource.error_presentation_factory', new Definition(ErrorPresentationFactory::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddErrorPresentationFactoryPass());
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $factoryDef = new Definition(ErrorPresentationFactoryInterface::class);
        $factoryDef->addTag('resource.error_presentation');

        $this->container->setDefinition('factory.test', $factoryDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('fivelab.resource.error_presentation_factory', 'add', [
            new Reference('factory.test'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotImplementInterface(): void
    {
        $factoryDef = new Definition(\stdClass::class);
        $factoryDef->addTag('resource.error_presentation');

        $this->container->setDefinition('factory.test', $factoryDef);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot compile error presentation factory with id "factory.test".');

        $this->compile();
    }
}
