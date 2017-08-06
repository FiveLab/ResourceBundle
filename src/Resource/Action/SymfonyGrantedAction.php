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

namespace FiveLab\Bundle\ResourceBundle\Resource\Action;

use FiveLab\Component\Resource\Resource\Action\ActionInterface;
use FiveLab\Component\Resource\Resource\Action\Method;
use FiveLab\Component\Resource\Resource\Href\HrefInterface;

/**
 * Action for support symfony security grant check.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedAction implements ActionInterface
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * @var object
     */
    private $object;

    /**
     * @var ActionInterface
     */
    private $originAction;

    /**
     * Constructor.
     *
     * @param ActionInterface $originAction
     * @param string          $attribute
     * @param mixed           $object
     */
    public function __construct(ActionInterface $originAction, string $attribute, $object = null)
    {
        $this->attribute = $attribute;
        $this->object = $object;
        $this->originAction = $originAction;
    }

    /**
     * Get origin action
     *
     * @return ActionInterface
     */
    public function getOriginAction(): ActionInterface
    {
        return $this->originAction;
    }

    /**
     * Get the attribute
     *
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * Get the object
     *
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->originAction->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(): HrefInterface
    {
        return $this->originAction->getHref();
    }

    /**
     * {@inheritdoc}
     */
    public function setHref(HrefInterface $href): void
    {
        $this->originAction->setHref($href);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): Method
    {
        return $this->originAction->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod(Method $method): void
    {
        $this->originAction->setMethod($method);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->originAction->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): void
    {
        $this->originAction->setAttributes($attributes);
    }
}
