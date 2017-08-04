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

use FiveLab\Bundle\ResourceBundle\Resource\Relation\SymfonyGrantedRelation;
use FiveLab\Component\Resource\Resource\RelatedResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Listener for check access rights before render relations.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedRelationListener
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Check the grants to relations
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
            if (!$relation instanceof SymfonyGrantedRelation) {
                continue;
            }

            if (!$this->authorizationChecker->isGranted($relation->getAttribute(), $relation->getObject())) {
                $resource->removeRelation($relation);
            }
        }
    }
}
