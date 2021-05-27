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

namespace FiveLab\Bundle\ResourceBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Throw this exception if the resource is't valid.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceNotValidException extends \RuntimeException
{
    /**
     * @var ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    private ConstraintViolationListInterface $violations;

    /**
     * Constructor.
     *
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $violations
     * @param string                                                         $message
     * @param \Throwable|null                                                $previous
     * @param int                                                            $code
     */
    public function __construct(ConstraintViolationListInterface $violations, $message = 'Resource is\'t valid.', \Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);

        $this->violations = $violations;
    }

    /**
     * Get the violations
     *
     * @return ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
