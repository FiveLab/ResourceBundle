<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\Error;

use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryChain;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use FiveLab\Component\Resource\Presentation\PresentationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ErrorPresentationFactoryChainTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $factory1 = $this->createMock(ErrorPresentationFactoryInterface::class);
        $factory2 = $this->createMock(ErrorPresentationFactoryInterface::class);

        $chain = new ErrorPresentationFactoryChain();
        $chain->add($factory1);
        $chain->add($factory2);

        $exception = new \Exception('some');
        $presentation = $this->createMock(PresentationInterface::class);

        $factory1->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn(null);

        $factory2->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn($presentation);

        $result = $chain->create($exception);

        self::assertEquals($presentation, $result);
    }

    /**
     * @test
     */
    public function shouldReturnNullIfNotCreated(): void
    {
        $chain = new ErrorPresentationFactoryChain();
        $result = $chain->create(new \Exception());

        self::assertNull($result);
    }
}
