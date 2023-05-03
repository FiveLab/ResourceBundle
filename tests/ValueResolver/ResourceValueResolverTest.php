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

namespace FiveLab\Bundle\ResourceBundle\Tests\ValueResolver;

use FiveLab\Bundle\ResourceBundle\Exception\MissingContentInRequestException;
use FiveLab\Bundle\ResourceBundle\ValueResolver\ResourceValueResolver;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Context\ResourceSerializationContext;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceValueResolverTest extends TestCase
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
     * @var ResourceValueResolver
     */
    private ResourceValueResolver $resolver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->serializerResolver = $this->createMock(ResourceSerializerResolverInterface::class);
        $this->serializationContextCollector = $this->createMock(SerializationContextCollectorInterface::class);
        $this->resolver = new ResourceValueResolver($this->serializerResolver, $this->serializationContextCollector);
    }

    /**
     * @test
     */
    public function shouldSupportForResource(): void
    {
        $argument = new ArgumentMetadata('foo', ResourceInterface::class, false, false, null);

        $supports = $this->resolver->supports(new Request(), $argument);

        self::assertTrue($supports);
    }

    /**
     * @test
     */
    public function shouldNotSupportsIfPassNoResource(): void
    {
        $argument = new ArgumentMetadata('foo', \stdClass::class, false, false, null);

        $supports = $this->resolver->supports(new Request(), $argument);

        self::assertFalse($supports);
    }

    /**
     * @test
     */
    public function shouldSuccessConvertForOptionalValueWithoutContent(): void
    {
        $request = $this->createRequest('');

        $argument = new ArgumentMetadata('bar', ResourceInterface::class, false, true, null, true);

        $result = $this->resolver->resolve($request, $argument);

        self::assertEquals([null], $result);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnConvertForRequiredValueWithoutContent(): void
    {
        $request = $this->createRequest('');

        $argument = new ArgumentMetadata('bar', ResourceInterface::class, false, false, null, false);

        $this->expectException(MissingContentInRequestException::class);
        $this->expectExceptionMessage('Missing content in request.');

        $this->resolver->resolve($request, $argument);
    }

    /**
     * @test
     */
    public function shouldSuccessConvert(): void
    {
        $request = $this->createRequest('some-foo-content', 'application/some');
        $context = new ResourceSerializationContext([]);

        $serializer = $this->createMock(ResourceSerializerInterface::class);
        $resource = $this->createMock(ResourceInterface::class);

        $argument = new ArgumentMetadata('bar', ResourceInterface::class, false, false, null, false);

        $this->serializationContextCollector->expects(self::once())
            ->method('collect')
            ->willReturn($context);

        $this->serializerResolver->expects(self::once())
            ->method('resolveByMediaType')
            ->with(ResourceInterface::class, 'application/some')
            ->willReturn($serializer);

        $serializer->expects(self::once())
            ->method('deserialize')
            ->with('some-foo-content', ResourceInterface::class, $context)
            ->willReturn($resource);

        $resolved = $this->resolver->resolve($request, $argument);

        self::assertEquals([$resource], $resolved);
    }

    /**
     * Create the request with content
     *
     * @param string $content
     * @param string $mediaType
     *
     * @return Request
     */
    private function createRequest(string $content, string $mediaType = ''): Request
    {
        return new Request(
            [],
            [],
            [],
            [],
            [],
            [
                'HTTP_Content-Type' => $mediaType,
            ],
            $content
        );
    }
}
