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

namespace FiveLab\Bundle\ResourceBundle\Resource\Error;

use FiveLab\Component\Resource\Presentation\PresentationInterface;

/**
 * The chain for collect all error presentation factories.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ErrorPresentationFactory implements ErrorPresentationFactoryInterface
{
    /**
     * @var \SplQueue<ErrorPresentationFactoryInterface>
     */
    private \SplQueue $factories;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->factories = new \SplQueue();
    }

    /**
     * Add the error presentation factory to chain.
     *
     * @param ErrorPresentationFactoryInterface $factory
     */
    public function add(ErrorPresentationFactoryInterface $factory): void
    {
        $this->factories->enqueue($factory);
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Throwable $error): ?PresentationInterface
    {
        foreach ($this->factories as $factory) {
            $errorPresentation = $factory->create($error);

            if ($errorPresentation) {
                return $errorPresentation;
            }
        }

        return null;
    }
}
