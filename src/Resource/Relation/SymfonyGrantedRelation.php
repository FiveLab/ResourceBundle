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

namespace FiveLab\Bundle\ResourceBundle\Resource\Relation;

use FiveLab\Component\Resource\Resource\Href\HrefInterface;
use FiveLab\Component\Resource\Resource\Relation\RelationInterface;

/**
 * Relation for support symfony security grant check.
 *
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedRelation implements RelationInterface
{
    /**
     * @var string
     */
    private string $attribute;

    /**
     * @var object|null
     */
    private ?object $object;

    /**
     * @var RelationInterface
     */
    private RelationInterface $originRelation;

    /**
     * Constructor.
     *
     * @param RelationInterface $originRelation
     * @param string            $attribute
     * @param object|null       $object
     */
    public function __construct(RelationInterface $originRelation, string $attribute, object $object = null)
    {
        $this->originRelation = $originRelation;
        $this->attribute = $attribute;
        $this->object = $object;
    }

    /**
     * Get the origin relation
     *
     * @return RelationInterface
     */
    public function getOriginRelation(): RelationInterface
    {
        return $this->originRelation;
    }

    /**
     * Get the attribute for check grant
     *
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * Get the object for check grants
     *
     * @return object|null
     */
    public function getObject(): ?object
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->originRelation->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(): HrefInterface
    {
        return $this->originRelation->getHref();
    }

    /**
     * {@inheritdoc}
     */
    public function setHref(HrefInterface $href): void
    {
        $this->originRelation->setHref($href);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->originRelation->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes(array $attributes): void
    {
        $this->originRelation->setAttributes($attributes);
    }
}
