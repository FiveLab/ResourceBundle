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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddResourceSerializerPass;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolver;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use FiveLab\Component\Resource\Serializer\Serializer;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class AddResourceSerializerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $symfonySerializerDef = new Definition(SymfonySerializer::class);
        $symfonySerializerDef->setArguments([
            [new Reference('sf_normalizer_1'), new Reference('sf_normalizer_2')],
            [new Reference('sf_encoder_1'), new Reference('sf_encoder_2')],
        ]);

        $this->container->setDefinition('serializer', $symfonySerializerDef);

        $this->container->setDefinition('fivelab.resource.serializer_resolver', new Definition(ResourceSerializerResolver::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddResourceSerializerPass());
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $serializerDef = new Definition(Serializer::class);
        $serializerDef->setArguments([
            [new Reference('normalizer_1'), new Reference('normalizer_2')],
            [],
        ]);

        $this->container->setDefinition('resource.serializer.inner', $serializerDef);

        $resourceSerializerDef = new Definition(ResourceSerializerInterface::class);
        $resourceSerializerDef->setArguments([new Reference('resource.serializer.inner')]);
        $resourceSerializerDef->addTag('resource.serializer', ['supportable' => 'foo.bar']);

        $this->container->setDefinition('resource.serializer', $resourceSerializerDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('resource.serializer.inner', 0, [
            new Reference('normalizer_1'),
            new Reference('normalizer_2'),
            new Reference('sf_normalizer_1'),
            new Reference('sf_normalizer_2'),
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('fivelab.resource.serializer_resolver', 'add', [
            new Reference('foo.bar'),
            new Reference('resource.serializer'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotImplementInterface(): void
    {
        $resourceSerializerDef = new Definition(\stdClass::class);
        $resourceSerializerDef->addTag('resource.serializer');

        $this->container->setDefinition('resource.serializer', $resourceSerializerDef);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource serializer with service id "resource.serializer".');

        $this->compile();
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotExistSupportable(): void
    {
        $resourceSerializerDef = new Definition(ResourceSerializerInterface::class);
        $resourceSerializerDef->setArguments([new Reference('serializer')]);
        $resourceSerializerDef->addTag('resource.serializer');

        $this->container->setDefinition('resource.serializer', $resourceSerializerDef);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource serializer with service id "resource.serializer".');

        $this->compile();
    }
}
