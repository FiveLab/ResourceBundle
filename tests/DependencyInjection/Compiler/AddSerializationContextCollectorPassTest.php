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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddSerializationContextCollectorPass;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorChain;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddSerializationContextCollectorPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $collectorDefinition;

    /**
     * @var AddSerializationContextCollectorPass
     */
    private $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->collectorDefinition = new Definition(SerializationContextCollectorChain::class);
        $this->container->setDefinition('fivelab.resource.serializer.context_collector', $this->collectorDefinition);

        $this->compiler = new AddSerializationContextCollectorPass();
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $collector = $this->createMock(SerializationContextCollectorInterface::class);
        $collectorClass = get_class($collector);

        $this->container->getParameterBag()->add([
            'collector.class' => $collectorClass,
        ]);

        $collectorDefinition = (new Definition('%collector.class%'))
            ->addTag('resource.serializer.collector');

        $this->container->setDefinition('collector.custom', $collectorDefinition);

        $this->compiler->process($this->container);
        $calls = $this->collectorDefinition->getMethodCalls();

        self::assertEquals([
            [
                'add',
                [
                    new Reference('collector.custom'),
                ],
            ],
        ], $calls);
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can't compile resource serialization collector with service id "collector.custom".
     */
    public function shouldFailIfCollectorNotImplementInterface(): void
    {
        $collectorDefinition = (new Definition(\stdClass::class))
            ->addTag('resource.serializer.collector');

        $this->container->setDefinition('collector.custom', $collectorDefinition);

        $this->compiler->process($this->container);
    }
}
