<?php

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
use FiveLab\Component\Exception\ViolationListException;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent;
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
     * @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->expectException(ViolationListException::class);
        $this->expectExceptionMessage('Not valid. [path]: some-message;');

        $resource = $this->createMock(ResourceInterface::class);
        $event = $this->createEvent($resource);

        $this->validator->expects(self::once())
            ->method('validate')
            ->with($resource)
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('some-message', 'templated message', [], 'root', 'path', 'some'),
            ]));

        $this->listener->onKernelControllerArguments($event);
    }

    /**
     * Create event
     *
     * @param array ...$arguments
     *
     * @return FilterControllerArgumentsEvent
     */
    private function createEvent(...$arguments): FilterControllerArgumentsEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new FilterControllerArgumentsEvent(
            $kernel,
            [$this, 'controllerAction'],
            $arguments,
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
