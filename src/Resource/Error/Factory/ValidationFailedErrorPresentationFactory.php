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

namespace FiveLab\Bundle\ResourceBundle\Resource\Error\Factory;

use FiveLab\Bundle\ResourceBundle\Exception\ResourceNotValidException;
use FiveLab\Bundle\ResourceBundle\Resource\Error\ErrorPresentationFactoryInterface;
use FiveLab\Component\Resource\Presentation\PresentationFactory;
use FiveLab\Component\Resource\Presentation\PresentationInterface;
use FiveLab\Component\Resource\Resource\Error\ErrorCollection;
use FiveLab\Component\Resource\Resource\Error\ErrorResource;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Validation factory for create error presentation via violation list.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ValidationFailedErrorPresentationFactory implements ErrorPresentationFactoryInterface
{
    /**
     * @var string
     */
    private string $message;

    /**
     * @var string|int|null
     */
    private $reason;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param string|int|null $reason
     */
    public function __construct(string $message = 'Validation failed.', $reason = null)
    {
        $this->message = $message;
        $this->reason = $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Throwable $error): ?PresentationInterface
    {
        if (!$error instanceof ResourceNotValidException) {
            return null;
        }

        $violations = $error->getViolations();
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $errors[] = new ErrorResource(
                (string) $violation->getMessage(),
                $violation->getCode(),
                $violation->getPropertyPath()
            );
        }

        $errorResource = new ErrorCollection($this->message, $this->reason);
        $errorResource->addErrors(...$errors);

        return PresentationFactory::badRequest($errorResource);
    }
}
