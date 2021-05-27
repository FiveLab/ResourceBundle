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

namespace FiveLab\Bundle\ResourceBundle\Tests\ParamConverter;

use FiveLab\Bundle\ResourceBundle\Exception\MissingContentInRequestException;
use FiveLab\Bundle\ResourceBundle\ParamConverter\ResourceParamConverter;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Context\ResourceSerializationContext;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use FiveLab\Component\Resource\Serializer\ResourceSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceParamConverterTest extends TestCase
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
     * @var ResourceParamConverter
     */
    private ResourceParamConverter $converter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->serializerResolver = $this->createMock(ResourceSerializerResolverInterface::class);
        $this->serializationContextCollector = $this->createMock(SerializationContextCollectorInterface::class);
        $this->converter = new ResourceParamConverter($this->serializerResolver, $this->serializationContextCollector);
    }

    /**
     * @test
     */
    public function shouldSupportForResource(): void
    {
        $config = new ParamConverter([
            'class' => ResourceInterface::class,
        ]);

        $supports = $this->converter->supports($config);

        self::assertTrue($supports);
    }

    /**
     * @test
     */
    public function shouldNotSupportsIfPassNoResource(): void
    {
        $config = new ParamConverter([
            'class' => \stdClass::class,
        ]);

        $supports = $this->converter->supports($config);

        self::assertFalse($supports);
    }

    /**
     * @test
     */
    public function shouldSuccessConvertForOptionalValueWithoutContent(): void
    {
        $request = $this->createRequest('');
        $config = new ParamConverter([
            'class' => ResourceInterface::class,
            'name'  => 'some',
        ]);

        $config->setIsOptional(true);

        $this->converter->apply($request, $config);

        self::assertTrue($request->attributes->has('some'));
        self::assertNull($request->attributes->get('some'));
    }

    /**
     * @test
     */
    public function shouldThrowExceptionOnConvertForRequiredValueWithoutContent(): void
    {
        $this->expectException(MissingContentInRequestException::class);
        $this->expectExceptionMessage('Missing content in request.');

        $request = $this->createRequest('');
        $config = new ParamConverter([
            'class' => ResourceInterface::class,
            'name'  => 'some',
        ]);

        $this->converter->apply($request, $config);
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
        $config = new ParamConverter([
            'class' => ResourceInterface::class,
            'name'  => 'some',
        ]);

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

        $this->converter->apply($request, $config);

        self::assertTrue($request->attributes->has('some'));
        self::assertEquals($resource, $request->attributes->get('some'));
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
