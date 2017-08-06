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

use FiveLab\Bundle\ResourceBundle\Resource\Href\SymfonyRouteHref;
use FiveLab\Component\Resource\Resource\ActionedResourceInterface;
use FiveLab\Component\Resource\Resource\Href\Href;
use FiveLab\Component\Resource\Resource\Href\HrefInterface;
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

        if ($resource instanceof RelatedResourceInterface) {
            $this->fixForRelations($resource);
        }

        if ($resource instanceof ActionedResourceInterface) {
            $this->fixForActions($resource);
        }
    }

    /**
     * Fix links for actions
     *
     * @param ActionedResourceInterface $resource
     */
    private function fixForActions(ActionedResourceInterface $resource): void
    {
        $actions = $resource->getActions();

        foreach ($actions as $action) {
            $routeHref = $action->getHref();
            $href = $this->fixHref($routeHref);
            $action->setHref($href);
        }
    }

    /**
     * Fix links for relations
     *
     * @param RelatedResourceInterface $resource
     */
    private function fixForRelations(RelatedResourceInterface $resource): void
    {
        $relations = $resource->getRelations();

        foreach ($relations as $relation) {
            $routeHref = $relation->getHref();
            $href = $this->fixHref($routeHref);
            $relation->setHref($href);
        }
    }

    /**
     * Try to fix href
     *
     * @param HrefInterface $routeHref
     *
     * @return HrefInterface
     */
    private function fixHref(HrefInterface $routeHref): HrefInterface
    {
        if (!$routeHref instanceof SymfonyRouteHref) {
            return $routeHref;
        }

        $path = $this->urlGenerator->generate(
            $routeHref->getRouteName(),
            $routeHref->getRouteParameters(),
            $routeHref->getReferenceType()
        );

        return new Href(
            $path,
            $routeHref->isTemplated()
        );
    }
}
