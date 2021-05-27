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

namespace FiveLab\Bundle\ResourceBundle\Tests\EventListener;

use FiveLab\Bundle\ResourceBundle\EventListener\ExceptionListener;
use FiveLab\Bundle\ResourceBundle\EventListener\LoggingExceptionListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class LoggingExceptionListenerTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var ExceptionListener|MockObject
     */
    private ExceptionListener $originListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->originListener = $this->createMock(ExceptionListener::class);
    }

    /**
     * @test
     */
    public function shouldNotLogIfOriginNotProcess(): void
    {
        $event = $this->createEvent(new \Exception());
        $listener = new LoggingExceptionListener($this->originListener, $this->logger);

        $this->originListener->expects(self::once())
            ->method('onKernelException')
            ->with($event);

        $this->logger->expects(self::never())
            ->method('log');

        $listener->onKernelException($event);
    }

    /**
     * @test
     */
    public function shouldNotLogIfLoggerNotInjected(): void
    {
        $event = $this->createEvent(new \Exception());
        $listener = new LoggingExceptionListener($this->originListener);

        $this->originListener->expects(self::once())
            ->method('onKernelException')
            ->with($event)
            ->willReturnCallback(function (ExceptionEvent $event) {
                $event->setResponse(new Response());
            });

        $listener->onKernelException($event);
    }

    /**
     * @test
     */
    public function shouldSuccessLog(): void
    {
        $exception = new \Exception('some');
        $event = $this->createEvent($exception);
        $listener = new LoggingExceptionListener($this->originListener, $this->logger, 'error');

        $this->originListener->expects(self::once())
            ->method('onKernelException')
            ->with($event)
            ->willReturnCallback(function (ExceptionEvent $event) {
                $event->setResponse(new Response());
            });

        $this->logger->expects(self::once())
            ->method('log')
            ->with('error', self::logicalNot(self::isNull()), [
                'exception' => $exception,
            ]);

        $listener->onKernelException($event);
    }

    /**
     * Create event for testing exception listener
     *
     * @param \Exception $exception
     *
     * @return ExceptionEvent
     */
    private function createEvent(\Exception $exception): ExceptionEvent
    {
        $request = new Request();

        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }
}
