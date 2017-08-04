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

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Throw this exception if client not send request content but the controller require the input resource.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class MissingContentInRequestException extends BadRequestHttpException
{
    /**
     * Constructor.
     *
     * @param string          $message
     * @param \Exception|null $previous
     * @param int             $code
     */
    public function __construct($message = 'Missing content in request.', \Exception $previous = null, $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
