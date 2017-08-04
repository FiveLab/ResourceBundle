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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Compiler\AddErrorPresentationFactoryPass;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryChain;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class AddErrorPresentationFactoryPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $factoryDefinition;

    /**
     * @var AddErrorPresentationFactoryPass
     */
    private $compiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->factoryDefinition = new Definition(ErrorPresentationFactoryChain::class);
        $this->container->setDefinition('fivelab.resource.error_presentation_factory', $this->factoryDefinition);

        $this->compiler = new AddErrorPresentationFactoryPass();
    }

    /**
     * @test
     */
    public function shouldSuccessCompile(): void
    {
        $factory = $this->createMock(ErrorPresentationFactoryInterface::class);
        $factoryClass = get_class($factory);

        $this->container->getParameterBag()->add([
            'factory.class' => $factoryClass,
        ]);

        $factoryDefinition = (new Definition('%factory.class%'))
            ->addTag('resource.error_presentation');

        $this->container->setDefinition('factory.custom', $factoryDefinition);

        $this->compiler->process($this->container);
        $calls = $this->factoryDefinition->getMethodCalls();

        self::assertEquals([
            [
                'add',
                [
                    new Reference('factory.custom'),
                ],
            ],
        ], $calls);
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Cannot compile error presentation factory with id "factory.custom".
     */
    public function shouldFailIfFactoryNotImplementInterface(): void
    {
        $factoryDefinition = (new Definition(\stdClass::class))
            ->addTag('resource.error_presentation');

        $this->container->setDefinition('factory.custom', $factoryDefinition);

        $this->compiler->process($this->container);
    }
}
