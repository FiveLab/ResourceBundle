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

use FiveLab\Bundle\ResourceBundle\EventListener\ExceptionListener;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use FiveLab\Component\Resource\Presentation\PresentationFactory;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Context\ResourceSerializationContext;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerNotFoundException;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ExceptionListenerTest extends TestCase
{
    /**
     * @var ErrorPresentationFactoryInterface|MockObject
     */
    private $errorPresentationFactory;

    /**
     * @var ResourceSerializerResolverInterface|MockObject
     */
    private $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface|MockObject
     */
    private $serializationContextCollector;

    /**
     * @var ExceptionListener
     */
    private $exceptionListener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->errorPresentationFactory = $this->createMock(ErrorPresentationFactoryInterface::class);
        $this->serializerResolver = $this->createMock(ResourceSerializerResolverInterface::class);
        $this->serializationContextCollector = $this->createMock(SerializationContextCollectorInterface::class);

        $this->exceptionListener = new ExceptionListener(
            $this->errorPresentationFactory,
            $this->serializerResolver,
            $this->serializationContextCollector,
            '_debug',
            true
        );
    }

    /**
     * @test
     */
    public function shouldNotProcessIfPassDebugParameter(): void
    {
        $exception = new \Exception('some');

        $event = $this->createEvent(['some'], $exception, ['_debug' => null]);

        $this->exceptionListener->onKernelException($event);

        self::assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function shouldNotProcessIfFactoryNotReturnsPresentation(): void
    {
        $exception = new \Exception('some');

        $this->errorPresentationFactory->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn(null);

        $event = $this->createEvent(['some'], $exception);

        $this->exceptionListener->onKernelException($event);

        self::assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function shouldNotProcessIfFactoryReturnPresentationWithoutResource(): void
    {
        $exception = new \Exception('some');

        $this->errorPresentationFactory->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn(PresentationFactory::create(500));

        $event = $this->createEvent(['some'], $exception);

        $this->exceptionListener->onKernelException($event);

        self::assertTrue($event->hasResponse());
        self::assertEquals(new Response('', 500), $event->getResponse());
    }

    /**
     * @test
     */
    public function shouldNotProcessIfSerializerNotFound(): void
    {
        $exception = new \Exception();
        $resource = $this->createMock(ResourceInterface::class);

        $this->errorPresentationFactory->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn(PresentationFactory::create(500, $resource));

        $this->serializerResolver->expects(self::once())
            ->method('resolveByMediaTypes')
            ->with(get_class($resource), ['application/some'])
            ->willThrowException(new ResourceSerializerNotFoundException());

        $event = $this->createEvent(['application/some'], $exception);

        $this->exceptionListener->onKernelException($event);

        self::assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function shouldSuccessProcess(): void
    {
        $exception = new \Exception();
        $resource = $this->createMock(ResourceInterface::class);
        $serializer = $this->createMock(ResourceSerializerInterface::class);
        $context = $this->createMock(ResourceSerializationContext::class);

        $this->serializationContextCollector->expects(self::once())
            ->method('collect')
            ->willReturn($context);

        $this->errorPresentationFactory->expects(self::once())
            ->method('create')
            ->with($exception)
            ->willReturn(PresentationFactory::create(404, $resource));

        $this->serializerResolver->expects(self::once())
            ->method('resolveByMediaTypes')
            ->with(get_class($resource), ['application/some'])
            ->willReturnCallback(function ($resourceClass, $acceptMediaTypes, &$acceptableMediaType) use ($serializer) {
                $acceptableMediaType = 'application/some';

                return $serializer;
            });

        $event = $this->createEvent(['application/some'], $exception);

        $serializer->expects(self::once())
            ->method('serialize')
            ->with($resource, $context)
            ->willReturn('serialized-error');

        $this->exceptionListener->onKernelException($event);

        self::assertTrue($event->hasResponse());
        self::assertEquals(404, $event->getResponse()->getStatusCode());
        self::assertEquals('serialized-error', $event->getResponse()->getContent());
        self::assertEquals('application/some', $event->getResponse()->headers->get('Content-Type'));
    }

    /**
     * Create event for testing exception listener
     *
     * @param array      $acceptableMediaTypes
     * @param \Exception $exception
     * @param array      $query
     *
     * @return GetResponseForExceptionEvent
     */
    private function createEvent(array $acceptableMediaTypes, \Exception $exception, array $query = []): GetResponseForExceptionEvent
    {
        $request = new Request(
            $query,
            [],
            [],
            [],
            [],
            [
                'HTTP_Accept' => $acceptableMediaTypes,
            ]
        );

        $kernel = $this->createMock(HttpKernelInterface::class);

        return new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }
}
