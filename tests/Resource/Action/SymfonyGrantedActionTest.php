<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\Action;

use FiveLab\Bundle\ResourceBundle\Resource\Action\SymfonyGrantedAction;
use FiveLab\Component\Resource\Resource\Action\ActionInterface;
use FiveLab\Component\Resource\Resource\Action\Method;
use FiveLab\Component\Resource\Resource\Href\HrefInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedActionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessGetOriginAction(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals($originAction, $action->getOriginAction());
    }

    /**
     * @test
     */
    public function shouldSuccessGetAttribute(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals('SOME', $action->getAttribute());
    }

    /**
     * @test
     */
    public function shouldSuccessGetObject(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $action = new SymfonyGrantedAction($originAction, 'SOME', (object) ['field' => 'value']);

        self::assertEquals((object) ['field' => 'value'], $action->getObject());
    }

    /**
     * @test
     */
    public function shouldSuccessGetName(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('getName')
            ->willReturn('some name');

        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals('some name', $action->getName());
    }

    /**
     * @test
     */
    public function shouldSuccessGetHref(): void
    {
        $href = $this->createMock(HrefInterface::class);
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('getHref')
            ->willReturn($href);

        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals($href, $action->getHref());
    }

    /**
     * @test
     */
    public function shouldSuccessSetHref(): void
    {
        $href = $this->createMock(HrefInterface::class);
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('setHref')
            ->with($href);

        $action = new SymfonyGrantedAction($originAction, 'SOME');
        $action->setHref($href);
    }

    /**
     * @test
     */
    public function shouldSuccessGetAttributes(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('getAttributes')
            ->willReturn(['some']);

        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals(['some'], $action->getAttributes());
    }

    /**
     * @test
     */
    public function shouldSuccessSetAttributes(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('setAttributes')
            ->with(['some']);

        $action = new SymfonyGrantedAction($originAction, 'SOME');
        $action->setAttributes(['some']);
    }

    /**
     * @test
     */
    public function shouldSuccessGetMethod(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('getMethod')
            ->willReturn(Method::post());

        $action = new SymfonyGrantedAction($originAction, 'SOME');

        self::assertEquals(Method::post(), $action->getMethod());
    }

    /**
     * @test
     */
    public function shouldSuccessSetMethod(): void
    {
        $originAction = $this->createMock(ActionInterface::class);
        $originAction->expects(self::once())
            ->method('setMethod')
            ->with(Method::post());

        $action = new SymfonyGrantedAction($originAction, 'SOME');

        $action->setMethod(Method::post());
    }
}
