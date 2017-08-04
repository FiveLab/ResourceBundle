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
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * The listener for logging exception.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class LoggingExceptionListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $level;

    /**
     * @var ExceptionListener
     */
    private $originListener;

    /**
     * Constructor.
     *
     * @param ExceptionListener $exceptionListener
     * @param LoggerInterface   $logger
     * @param string            $level
     */
    public function __construct(
        ExceptionListener $exceptionListener,
        LoggerInterface $logger = null,
        string $level = LogLevel::CRITICAL
    ) {
        $this->logger = $logger;
        $this->originListener = $exceptionListener;
        $this->level = $level;
    }

    /**
     * Handle the exception and log message.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $this->originListener->onKernelException($event);

        if (!$event->hasResponse()) {
            // The origin listener not processed.
            return;
        }

        $this->logException($event->getException());
    }

    /**
     * Log exception
     *
     * @param \Exception $exception
     */
    protected function logException(\Exception $exception): void
    {
        if (!$this->logger) {
            return;
        }

        $message = sprintf(
            'Exception thrown when handling an exception (%s: %s at %s line %s)',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $this->logger->log($this->level, $message, [
            'exception' => $exception,
        ]);
    }
}
