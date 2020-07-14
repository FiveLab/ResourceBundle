<?php

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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddResourceAssemblerPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $resolverDefinition;

    /**
     * @var AddResourceAssemblerPass
     */
    private $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->resolverDefinition = new Definition(ResourceAssemblerResolver::class);
        $this->container->setDefinition('fivelab.resource.assembler_resolver', $this->resolverDefinition);

        $this->compiler = new AddResourceAssemblerPass();
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $assembler = $this->createMock(ResourceAssemblerInterface::class);
        $assemblerClass = get_class($assembler);

        $this->container->getParameterBag()->add([
            'assembler.class' => $assemblerClass,
        ]);

        $factoryDefinition = (new Definition('%assembler.class%'))
            ->addTag('resource.assembler', ['supportable' => 'resource.assembler.supportable']);

        $this->container->setDefinition('assembler.custom', $factoryDefinition);

        $this->compiler->process($this->container);
        $calls = $this->resolverDefinition->getMethodCalls();

        self::assertEquals([
            [
                'add',
                [
                    new Reference('resource.assembler.supportable'),
                    new Reference('assembler.custom'),
                ],
            ],
        ], $calls);
    }

    /**
     * @test
     */
    public function shouldFailIfSupportableNotProvided(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource assembler with service id "assembler.custom".');

        $assembler = $this->createMock(ResourceAssemblerInterface::class);
        $assemblerClass = get_class($assembler);

        $this->container->getParameterBag()->add([
            'assembler.class' => $assemblerClass,
        ]);

        $factoryDefinition = (new Definition('%assembler.class%'))
            ->addTag('resource.assembler');

        $this->container->setDefinition('assembler.custom', $factoryDefinition);

        $this->compiler->process($this->container);
    }

    /**
     * @test
     */
    public function shouldFailIfAssemblerNotSupportRequiredInterface(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource assembler with service id "assembler.custom".');

        $factoryDefinition = (new Definition(\stdClass::class))
            ->addTag('resource.assembler', ['supportable' => 'resource.assembler.supportable']);

        $this->container->setDefinition('assembler.custom', $factoryDefinition);

        $this->compiler->process($this->container);
    }
}
