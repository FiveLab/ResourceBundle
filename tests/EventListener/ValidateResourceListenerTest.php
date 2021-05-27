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

use FiveLab\Bundle\ResourceBundle\EventListener\ValidateResourceListener;
use FiveLab\Bundle\ResourceBundle\Exception\ResourceNotValidException;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ValidateResourceListenerTest extends TestCase
{
    /**
     * @var ValidatorInterface|MockObject
     */
    private $validator;

    /**
     * @var ValidateResourceListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->listener = new ValidateResourceListener($this->validator);
    }

    /**
     * The stub for controller action
     */
    public function controllerAction(): void
    {
    }

    /**
     * @test
     */
    public function shouldSuccessProcess(): void
    {
        $event = $this->createEvent(new \stdClass(), new \stdClass());

        $this->validator->expects(self::never())
            ->method('validate');

        $this->listener->onKernelControllerArguments($event);
    }

    /**
     * @test
     */
    public function shouldFailProcessIfResourceIsNotValid(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $event = $this->createEvent($resource);

        $this->validator->expects(self::once())
            ->method('validate')
            ->with($resource)
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('some-message', 'templated message', [], 'root', 'path', 'some'),
            ]));

        $this->expectException(ResourceNotValidException::class);
        $this->expectExceptionMessage('Resource is\'t valid.');

        $this->listener->onKernelControllerArguments($event);
    }

    /**
     * Create event
     *
     * @param mixed ...$arguments
     *
     * @return ControllerArgumentsEvent
     */
    private function createEvent(...$arguments): ControllerArgumentsEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ControllerArgumentsEvent(
            $kernel,
            [$this, 'controllerAction'],
            $arguments,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
