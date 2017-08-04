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

namespace FiveLab\Bundle\ResourceBundle\Resource\EventListener;

use FiveLab\Bundle\ResourceBundle\Resource\Relation\Href\SymfonyRouteHref;
use FiveLab\Component\Resource\Resource\Href\Href;
use FiveLab\Component\Resource\Resource\RelatedResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Event listener for convert symfony href's to relation href's.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class GenerateSymfonyRouteHrefListener
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Call to this method before normalization
     *
     * @param BeforeNormalizationEvent $event
     */
    public function onBeforeNormalization(BeforeNormalizationEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource instanceof RelatedResourceInterface) {
            return;
        }

        $relations = $resource->getRelations();

        foreach ($relations as $relation) {
            $routeHref = $relation->getHref();

            if (!$routeHref instanceof SymfonyRouteHref) {
                continue;
            }

            $path = $this->urlGenerator->generate(
                $routeHref->getRouteName(),
                $routeHref->getRouteParameters(),
                $routeHref->getReferenceType()
            );

            $href = new Href(
                $path,
                $routeHref->isTemplated()
            );

            $relation->setHref($href);
        }
    }
}
