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

namespace FiveLab\Bundle\ResourceBundle\EventListener;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * The listener for logging exception.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class LoggingExceptionListener
{
    /**
     * @var LoggerInterface|null
     */
    protected ?LoggerInterface $logger;

    /**
     * @var string
     */
    protected string $level;

    /**
     * @var ExceptionListener
     */
    private ExceptionListener $originListener;

    /**
     * Constructor.
     *
     * @param ExceptionListener    $exceptionListener
     * @param LoggerInterface|null $logger
     * @param string               $level
     */
    public function __construct(ExceptionListener $exceptionListener, LoggerInterface $logger = null, string $level = LogLevel::CRITICAL)
    {
        $this->logger = $logger;
        $this->originListener = $exceptionListener;
        $this->level = $level;
    }

    /**
     * Handle the exception and log message.
     *
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $this->originListener->onKernelException($event);

        if (!$event->hasResponse()) {
            // The origin listener not processed.
            return;
        }

        $this->logException($event->getThrowable());
    }

    /**
     * Log exception
     *
     * @param \Throwable $error
     */
    protected function logException(\Throwable $error): void
    {
        if (!$this->logger) {
            return;
        }

        $message = \sprintf(
            'Exception thrown when handling an exception (%s: %s at %s line %s)',
            \get_class($error),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );

        $this->logger->log($this->level, $message, [
            'exception' => $error,
        ]);
    }
}
