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

use FiveLab\Bundle\ResourceBundle\Exception\ResourceNotValidException;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The listener for validate input resources.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ValidateResourceListener
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * Constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Listen kernel.controller_arguments event for validate input resources
     *
     * @param ControllerArgumentsEvent $event
     *
     * @throws ResourceNotValidException
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $arguments = $event->getArguments();

        foreach ($arguments as $argument) {
            if ($argument instanceof ResourceInterface) {
                $violationList = $this->validator->validate($argument);

                if (\count($violationList)) {
                    throw new ResourceNotValidException($violationList);
                }
            }
        }
    }
}
