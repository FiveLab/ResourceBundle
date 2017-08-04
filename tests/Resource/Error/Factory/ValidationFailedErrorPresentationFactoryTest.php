<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\Error\Factory;

use FiveLab\Bundle\ResourceBundle\Resource\Error\Factory\ValidationFailedErrorPresentationFactory;
use FiveLab\Component\Exception\ViolationListException;
use FiveLab\Component\Resource\Resource\Error\ErrorCollection;
use FiveLab\Component\Resource\Resource\Error\ErrorResource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ValidationFailedErrorPresentationFactoryTest extends TestCase
{
    /**
     * @var ValidationFailedErrorPresentationFactory
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new ValidationFailedErrorPresentationFactory('Validation failed.', 'ValidationFailed');
    }

    /**
     * @test
     */
    public function shouldNotProcessIfExceptionNotSupported(): void
    {
        $exception = new \Exception();

        $result = $this->factory->create($exception);

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $violation = new ConstraintViolation(
            'message',
            'message template',
            [],
            'root',
            'path',
            'invalid value'
        );

        $violationList = new ConstraintViolationList([$violation]);
        $exception = ViolationListException::create($violationList);

        $expectedError = new ErrorCollection('Validation failed.', 'ValidationFailed');
        $expectedError->addErrors(new ErrorResource('message', null, 'path'));

        $presentation = $this->factory->create($exception);

        self::assertEquals(400, $presentation->getStatusCode());
        self::assertEquals($expectedError, $presentation->getResource());
    }
}
