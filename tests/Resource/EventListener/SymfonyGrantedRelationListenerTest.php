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

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\EventListener;

use FiveLab\Bundle\ResourceBundle\Resource\EventListener\SymfonyGrantedRelationListener;
use FiveLab\Bundle\ResourceBundle\Resource\Relation\SymfonyGrantedRelation;
use FiveLab\Component\Resource\Resource\RelatedResourceInterface;
use FiveLab\Component\Resource\Resource\Relation\RelationCollection;
use FiveLab\Component\Resource\Resource\Relation\RelationInterface;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyGrantedRelationListenerTest extends TestCase
{
    /**
     * @var AuthorizationCheckerInterface|MockObject
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @var SymfonyGrantedRelationListener
     */
    private SymfonyGrantedRelationListener $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->listener = new SymfonyGrantedRelationListener($this->authorizationChecker);
    }

    /**
     * @test
     */
    public function shouldNotProcessIfPassRelatedResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->authorizationChecker->expects(self::never())
            ->method('isGranted');

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));
    }

    /**
     * @test
     */
    public function shouldSuccessProcess(): void
    {
        $resource = $this->createMock(RelatedResourceInterface::class);
        $notRelatedRelation = $this->createMock(RelationInterface::class);
        $relation1 = $this->createMock(RelationInterface::class);
        $grantRelation1 = new SymfonyGrantedRelation($relation1, 'SOME1', (object) ['some' => 'value']);

        $relation2 = $this->createMock(RelationInterface::class);
        $grantRelation2 = new SymfonyGrantedRelation($relation2, 'SOME2', (object) ['some' => 'value']);

        $resource->expects(self::once())
            ->method('getRelations')
            ->willReturn(new RelationCollection($notRelatedRelation, $grantRelation1, $grantRelation2));

        $this->authorizationChecker->expects(self::exactly(2))
            ->method('isGranted')
            ->with(self::logicalOr('SOME1', 'SOME2'), (object) ['some' => 'value'])
            ->willReturnCallback(static function ($attribute) {
                if ('SOME1' === $attribute) {
                    return false;
                }

                return true;
            });

        $resource->expects(self::once())
            ->method('removeRelation')
            ->with($grantRelation1);

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));
    }
}
