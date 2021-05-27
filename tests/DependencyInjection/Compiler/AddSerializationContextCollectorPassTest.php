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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddSerializationContextCollectorPass;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollector;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AddSerializationContextCollectorPassTest extends AbstractCompilerPassTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->setDefinition('fivelab.resource.serializer.context_collector', new Definition(SerializationContextCollector::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddSerializationContextCollectorPass());
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $collectorDef = new Definition(SerializationContextCollectorInterface::class);
        $collectorDef->addTag('resource.serializer.collector');

        $this->container->setDefinition('collector.test', $collectorDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('fivelab.resource.serializer.context_collector', 'add', [
            new Reference('collector.test'),
        ]);
    }

    /**
     * @test
     */
    public function shouldThrowErrorIfNotImplementInterface(): void
    {
        $collectorTest = new Definition(\stdClass::class);
        $collectorTest->addTag('resource.serializer.collector');

        $this->container->setDefinition('collector.test', $collectorTest);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can\'t compile resource serialization collector with service id "collector.test".');

        $this->compile();
    }
}
