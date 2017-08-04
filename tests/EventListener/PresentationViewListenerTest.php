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

use FiveLab\Bundle\ResourceBundle\EventListener\PresentationViewListener;
use FiveLab\Component\Resource\Presentation\PresentationFactory;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Context\ResourceSerializationContext;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class PresentationViewListenerTest extends TestCase
{
    /**
     * @var ResourceSerializerResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializationContextCollector;

    /**
     * @var PresentationViewListener
     */
    private $listener;

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
        $presentation = PresentationFactory::create(200);

        $event = $this->createEvent(['application/some'], $presentation);

        $this->listener->onKernelView($event);

        self::assertTrue($event->hasResponse());

        self::assertEquals(200, $event->getResponse()->getStatusCode());
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
     * @return GetResponseForControllerResultEvent
     */
    private function createEvent(array $acceptableMediaTypes, $controllerResult): GetResponseForControllerResultEvent
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

        return new GetResponseForControllerResultEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $controllerResult);
    }
}