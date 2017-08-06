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

namespace FiveLab\Bundle\ResourceBundle\Resource\Href;

use FiveLab\Component\Resource\Resource\Href\HrefInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Href for support symfony routing.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyRouteHref implements HrefInterface
{
    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @var int
     */
    private $referenceType;

    /**
     * @var bool
     */
    private $templated;

    /**
     * Constructor.
     *
     * @param string $routeName
     * @param array  $routeParameters
     * @param bool   $templated
     * @param int    $referenceType
     */
    public function __construct(
        string $routeName,
        array $routeParameters = [],
        bool $templated = false,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_URL
    ) {
        $this->routeName = $routeName;
        $this->routeParameters = $routeParameters;
        $this->referenceType = $referenceType;
        $this->templated = $templated;
    }

    /**
     * Get the name of route
     *
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * Get route parameters
     *
     * @return array
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    /**
     * Get the reference type
     *
     * @return int
     */
    public function getReferenceType(): int
    {
        return $this->referenceType;
    }

    /**
     * {@inheritdoc}
     */
    public function isTemplated(): bool
    {
        return $this->templated;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function getPath(): string
    {
        throw new \LogicException(sprintf(
            'The method %s does not support.',
            __METHOD__
        ));
    }
}
