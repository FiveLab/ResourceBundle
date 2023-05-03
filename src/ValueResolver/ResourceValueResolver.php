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

namespace FiveLab\Bundle\ResourceBundle\ValueResolver;

use FiveLab\Bundle\ResourceBundle\Exception\MissingContentInRequestException;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Value resolver for convert input request to input resource for next pass to controller.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var ResourceSerializerResolverInterface
     */
    private ResourceSerializerResolverInterface $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface
     */
    private SerializationContextCollectorInterface $serializationContextCollector;

    /**
     * Constructor.
     *
     * @param ResourceSerializerResolverInterface    $serializerResolver
     * @param SerializationContextCollectorInterface $contextCollector
     */
    public function __construct(ResourceSerializerResolverInterface $serializerResolver, SerializationContextCollectorInterface $contextCollector)
    {
        $this->serializerResolver = $serializerResolver;
        $this->serializationContextCollector = $contextCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() && \is_a($argument->getType(), ResourceInterface::class, true);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $content = (string) $request->getContent();

        if (!$content) {
            if ($argument->isNullable()) {
                return [null];
            }

            throw new MissingContentInRequestException();
        }

        $serializer = $this->serializerResolver->resolveByMediaType((string) $argument->getType(), (string) $request->headers->get('Content-Type'));
        $context = $this->serializationContextCollector->collect();

        $resource = $serializer->deserialize($content, (string) $argument->getType(), $context);

        return [$resource];
    }
}
