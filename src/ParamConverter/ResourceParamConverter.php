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

namespace FiveLab\Bundle\ResourceBundle\ParamConverter;

use FiveLab\Bundle\ResourceBundle\Exception\MissingContentInRequestException;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerNotFoundException;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Parameter converter for convert input request to input resource for next pass to controller.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceParamConverter implements ParamConverterInterface
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
     *
     * @throws MissingContentInRequestException
     * @throws ResourceSerializerNotFoundException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $content = (string) $request->getContent();

        if (!$content) {
            if ($configuration->isOptional()) {
                $request->attributes->set($configuration->getName(), null);

                return true;
            }

            throw new MissingContentInRequestException();
        }

        $serializer = $this->serializerResolver->resolveByMediaType($configuration->getClass(), (string) $request->headers->get('Content-Type'));
        $context = $this->serializationContextCollector->collect();

        $resource = $serializer->deserialize($content, $configuration->getClass(), $context);

        $request->attributes->set($configuration->getName(), $resource);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return \is_a($configuration->getClass(), ResourceInterface::class, true);
    }
}
