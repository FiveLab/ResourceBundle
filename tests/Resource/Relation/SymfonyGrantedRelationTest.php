<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\Relation;

use FiveLab\Bundle\ResourceBundle\Resource\Relation\SymfonyGrantedRelation;
use FiveLab\Component\Resource\Resource\Href\HrefInterface;
use FiveLab\Component\Resource\Resource\Relation\RelationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedRelationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessGetOriginRelation(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');

        self::assertEquals($originRelation, $relation->getOriginRelation());
    }

    /**
     * @test
     */
    public function shouldSuccessGetAttribute(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');

        self::assertEquals('SOME', $relation->getAttribute());
    }

    /**
     * @test
     */
    public function shouldSuccessGetObject(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $relation = new SymfonyGrantedRelation($originRelation, 'SOME', (object) ['field' => 'value']);

        self::assertEquals((object) ['field' => 'value'], $relation->getObject());
    }

    /**
     * @test
     */
    public function shouldSuccessGetName(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $originRelation->expects(self::once())
            ->method('getName')
            ->willReturn('some name');

        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');

        self::assertEquals('some name', $relation->getName());
    }

    /**
     * @test
     */
    public function shouldSuccessGetHref(): void
    {
        $href = $this->createMock(HrefInterface::class);
        $originRelation = $this->createMock(RelationInterface::class);
        $originRelation->expects(self::once())
            ->method('getHref')
            ->willReturn($href);

        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');

        self::assertEquals($href, $relation->getHref());
    }

    /**
     * @test
     */
    public function shouldSuccessSetHref(): void
    {
        $href = $this->createMock(HrefInterface::class);
        $originRelation = $this->createMock(RelationInterface::class);
        $originRelation->expects(self::once())
            ->method('setHref')
            ->with($href);

        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');
        $relation->setHref($href);
    }

    /**
     * @test
     */
    public function shouldSuccessGetAttributes(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $originRelation->expects(self::once())
            ->method('getAttributes')
            ->willReturn(['some']);

        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');

        self::assertEquals(['some'], $relation->getAttributes());
    }

    /**
     * @test
     */
    public function shouldSuccessSetAttributes(): void
    {
        $originRelation = $this->createMock(RelationInterface::class);
        $originRelation->expects(self::once())
            ->method('setAttributes')
            ->with(['some']);

        $relation = new SymfonyGrantedRelation($originRelation, 'SOME');
        $relation->setAttributes(['some']);
    }
}
