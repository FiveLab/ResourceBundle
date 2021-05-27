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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceAssemblerPass;
use FiveLab\Component\Resource\Assembler\Resolver\ResourceAssemblerResolver;
use FiveLab\Component\Resource\Assembler\ResourceAssemblerInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddResourceAssemblerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setDefinition('fivelab.resource.assembler_resolver', new Definition(ResourceAssemblerResolver::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddResourceAssemblerPass());
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $factoryDef = new Definition(ResourceAssemblerInterface::class);
        $factoryDef->addTag('resource.assembler', ['supportable' => 'assembler.supportable']);

        $this->container->setDefinition('assembler.test', $factoryDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('fivelab.resource.assembler_resolver', 'add', [
            new Reference('assembler.supportable'),
            new Reference('assembler.test'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotImplementInterface(): void
    {
        $assemblerDef = new Definition(\stdClass::class);
        $assemblerDef->addTag('resource.assembler', ['supportable' => 'assembler.supportable']);

        $this->container->setDefinition('assembler.test', $assemblerDef);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource assembler with service id "assembler.test".');

        $this->compile();
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotExistSupportable(): void
    {
        $assemblerDef = new Definition(ResourceAssemblerInterface::class);
        $assemblerDef->addTag('resource.assembler');

        $this->container->setDefinition('assembler.test', $assemblerDef);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource assembler with service id "assembler.test".');

        $this->compile();
    }
}
