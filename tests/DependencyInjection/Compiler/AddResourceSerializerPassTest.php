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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceSerializerPass;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolver;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddResourceSerializerPassTest extends TestCase
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
     * @var AddResourceSerializerPass
     */
    private $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->resolverDefinition = new Definition(ResourceSerializerResolver::class);
        $this->container->setDefinition('fivelab.resource.serializer_resolver', $this->resolverDefinition);

        $this->compiler = new AddResourceSerializerPass();
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $serializer = $this->createMock(ResourceSerializerInterface::class);
        $serializerClass = get_class($serializer);

        $this->container->getParameterBag()->add([
            'serializer.class' => $serializerClass,
        ]);

        $factoryDefinition = (new Definition('%serializer.class%'))
            ->addTag('resource.serializer', ['supportable' => 'resource.serializer.supportable']);

        $this->container->setDefinition('serializer.custom', $factoryDefinition);

        $this->compiler->process($this->container);
        $calls = $this->resolverDefinition->getMethodCalls();

        self::assertEquals([
            [
                'add',
                [
                    new Reference('resource.serializer.supportable'),
                    new Reference('serializer.custom'),
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
        $this->expectExceptionMessage('Can\'t compile resource serializer with service id "serializer.custom".');

        $serializer = $this->createMock(ResourceSerializerInterface::class);
        $serializerClass = get_class($serializer);

        $this->container->getParameterBag()->add([
            'serializer.class' => $serializerClass,
        ]);

        $factoryDefinition = (new Definition('%serializer.class%'))
            ->addTag('resource.serializer');

        $this->container->setDefinition('serializer.custom', $factoryDefinition);

        $this->compiler->process($this->container);
    }

    /**
     * @test
     */
    public function shouldFailIfSerializerNotSupportRequiredInterface(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource serializer with service id "serializer.custom".');

        $factoryDefinition = (new Definition(\stdClass::class))
            ->addTag('resource.serializer', ['supportable' => 'resource.serializer.supportable']);

        $this->container->setDefinition('serializer.custom', $factoryDefinition);

        $this->compiler->process($this->container);
    }
}
