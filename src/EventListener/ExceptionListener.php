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

use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use FiveLab\Component\Resource\Serializer\Context\Collector\SerializationContextCollectorInterface;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerNotFoundException;
use FiveLab\Component\Resource\Serializer\Resolver\ResourceSerializerResolverInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Event listener for listen kernel exceptions and set the response for send to client.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ExceptionListener
{
    /**
     * @var ErrorPresentationFactoryInterface
     */
    private $errorPresentationFactory;

    /**
     * @var ResourceSerializerResolverInterface
     */
    private $serializerResolver;

    /**
     * @var SerializationContextCollectorInterface
     */
    private $serializationContextCollector;

    /**
     * @var string
     */
    private $debugParameter;

    /**
     * @var bool
     */
    private $kernelDebug;

    /**
     * Constructor.
     *
     * @param ErrorPresentationFactoryInterface      $errorPresentationFactory
     * @param ResourceSerializerResolverInterface    $serializerResolver
     * @param SerializationContextCollectorInterface $serializationContextCollector
     * @param string                                 $debugParameter
     * @param bool                                   $kernelDebug
     */
    public function __construct(
        ErrorPresentationFactoryInterface $errorPresentationFactory,
        ResourceSerializerResolverInterface $serializerResolver,
        SerializationContextCollectorInterface $serializationContextCollector,
        string $debugParameter,
        bool $kernelDebug
    ) {
        $this->errorPresentationFactory = $errorPresentationFactory;
        $this->serializerResolver = $serializerResolver;
        $this->serializationContextCollector = $serializationContextCollector;
        $this->debugParameter = $debugParameter;
        $this->kernelDebug = $kernelDebug;
    }

    /**
     * Render the exception for sending to client
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->kernelDebug && $this->debugParameter && $request->query->has($this->debugParameter)) {
            return;
        }

        $exception = $event->getException();
        $errorPresentation = $this->errorPresentationFactory->create($exception);

        if (!$errorPresentation) {
            return;
        }

        if (!$errorPresentation->getResource()) {
            // The presentation not have resource. Send response with only status code.
            $event->setResponse(new Response('', $errorPresentation->getStatusCode()));

            return;
        }

        $acceptMediaType = null;

        try {
            $serializer = $this->serializerResolver->resolveByMediaTypes(
                get_class($errorPresentation->getResource()),
                $request->getAcceptableContentTypes(),
                $acceptMediaType
            );
        } catch (ResourceSerializerNotFoundException $e) {
            // Cannot resolve the serializer for accept media types.

            return;
        }

        $context = $this->serializationContextCollector->collect();
        $serializedData = $serializer->serialize($errorPresentation->getResource(), $context);

        $response = new Response($serializedData, $errorPresentation->getStatusCode(), [
            'Content-Type' => $acceptMediaType,
        ]);

        $event->setResponse($response);
    }
}
