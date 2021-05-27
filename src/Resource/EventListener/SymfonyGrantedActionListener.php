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

use FiveLab\Bundle\ResourceBundle\Resource\Action\SymfonyGrantedAction;
use FiveLab\Component\Resource\Resource\ActionedResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Listener for check access rights before render actions.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedActionListener
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private AuthorizationCheckerInterface $authorizationChecker;

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
     * Check the grants to actions
     *
     * @param BeforeNormalizationEvent $event
     */
    public function onBeforeNormalization(BeforeNormalizationEvent $event): void
    {
        $resource = $event->getResource();

        if (!$resource instanceof ActionedResourceInterface) {
            return;
        }

        $actions = $resource->getActions();

        foreach ($actions as $action) {
            if (!$action instanceof SymfonyGrantedAction) {
                continue;
            }

            if (!$this->authorizationChecker->isGranted($action->getAttribute(), $action->getObject())) {
                $resource->removeAction($action);
            }
        }
    }
}
