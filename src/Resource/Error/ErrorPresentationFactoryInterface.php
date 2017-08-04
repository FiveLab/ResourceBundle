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
 * All error presentation factories should implement this interface.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
interface ErrorPresentationFactoryInterface
{
    /**
     * Create the error presentation by exception.
     *
     * @param \Exception $exception
     *
     * @return PresentationInterface
     */
    public function create(\Exception $exception): ?PresentationInterface;
}
