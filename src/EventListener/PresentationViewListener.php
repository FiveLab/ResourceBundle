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

use FiveLab\Component\Resource\Presentation\PresentationInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerNotFoundException;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * The listener for listen kernel view event and serialize resource object for next send to client.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class PresentationViewListener
{
    /**
     * @var ResourceSerializerResolverInterface
     */
    private $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface
     */
    private $serializationContextCollector;

    /**
     * Constructor.
     *
     * @param ResourceSerializerResolverInterface    $serializerResolver
     * @param SerializationContextCollectorInterface $serializationContextCollector
     */
    public function __construct(
        ResourceSerializerResolverInterface $serializerResolver,
        SerializationContextCollectorInterface $serializationContextCollector
    ) {
        $this->serializerResolver = $serializerResolver;
        $this->serializationContextCollector = $serializationContextCollector;
    }

    /**
     * Handle for transform presentation to content for send to client
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \InvalidArgumentException
     * @throws ResourceSerializerNotFoundException
     */
    public function onKernelView(GetResponseForControllerResultEvent $event): void
    {
        $presentation = $event->getControllerResult();

        if (!$presentation instanceof PresentationInterface) {
            // Not the presentation instance
            return;
        }

        $acceptableMediaTypes = $event->getRequest()->getAcceptableContentTypes();
        $serializer = $this->serializerResolver->resolveByMediaTypes(
            get_class($presentation->getResource()),
            $acceptableMediaTypes,
            $acceptableMediaType
        );

        $data = null;

        if ($presentation->getResource()) {
            $serializationContext = $this->serializationContextCollector->collect();
            $data = $serializer->serialize($presentation->getResource(), $serializationContext);
        }

        $response = new Response($data, $presentation->getStatusCode());
        $response->headers->add([
            'Content-Type' => $acceptableMediaType,
        ]);

        $event->setResponse($response);
    }
}
