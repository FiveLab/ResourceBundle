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

use FiveLab\Bundle\ResourceBundle\EventListener\PresentationViewListener;
use FiveLab\Component\Resource\Presentation\PresentationFactory;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Context\ResourceSerializationContext;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class PresentationViewListenerTest extends TestCase
{
    /**
     * @var ResourceSerializerResolverInterface|MockObject
     */
    private ResourceSerializerResolverInterface $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface|MockObject
     */
    private SerializationContextCollectorInterface $serializationContextCollector;

    /**
     * @var PresentationViewListener
     */
    private PresentationViewListener $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->serializerResolver = $this->createMock(ResourceSerializerResolverInterface::class);
        $this->serializationContextCollector = $this->createMock(SerializationContextCollectorInterface::class);
        $this->listener = new PresentationViewListener($this->serializerResolver, $this->serializationContextCollector);
    }

    /**
     * @test
     */
    public function shouldNotProcessIfDataIsNoResource(): void
    {
        $event = $this->createEvent([], new \stdClass());

        $this->listener->onKernelView($event);

        self::assertFalse($event->hasResponse());
    }

    /**
     * @test
     */
    public function shouldSuccessProcessWithoutResource(): void
    {
        $presentation = PresentationFactory::create(204);

        $event = $this->createEvent(['application/some'], $presentation);

        $this->serializerResolver->expects(self::never())
            ->method('resolveByMediaTypes');

        $this->listener->onKernelView($event);

        self::assertTrue($event->hasResponse());

        self::assertEquals(204, $event->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function shouldSuccessProcess(): void
    {
        $resource = $this->createMock(ResourceInterface::class);
        $presentation = PresentationFactory::create(200, $resource);
        $serializer = $this->createMock(ResourceSerializerInterface::class);
        $context = $this->createMock(ResourceSerializationContext::class);

        $event = $this->createEvent(['application/some'], $presentation);

        $this->serializerResolver->expects(self::once())
            ->method('resolveByMediaTypes')
            ->with(get_class($resource), ['application/some'])
            ->willReturnCallback(function ($resourceClass, $acceptMediaTypes, &$acceptableMediaType) use ($serializer) {
                $acceptableMediaType = 'application/some';

                return $serializer;
            });

        $this->serializationContextCollector->expects(self::once())
            ->method('collect')
            ->willReturn($context);

        $serializer->expects(self::once())
            ->method('serialize')
            ->with($resource, $context)
            ->willReturn('serialized-resource');

        $this->listener->onKernelView($event);

        self::assertTrue($event->hasResponse());

        self::assertEquals(200, $event->getResponse()->getStatusCode());
        self::assertEquals('serialized-resource', $event->getResponse()->getContent());
        self::assertEquals('application/some', $event->getResponse()->headers->get('Content-Type'));
    }

    /**
     * Create the event
     *
     * @param array  $acceptableMediaTypes
     * @param object $controllerResult
     *
     * @return ViewEvent
     */
    private function createEvent(array $acceptableMediaTypes, $controllerResult): ViewEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'HTTP_Accept' => $acceptableMediaTypes,
            ]
        );

        return new ViewEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $controllerResult);
    }
}
