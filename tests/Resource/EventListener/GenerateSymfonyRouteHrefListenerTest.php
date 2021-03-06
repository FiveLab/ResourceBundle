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

use FiveLab\Bundle\ResourceBundle\Resource\EventListener\GenerateSymfonyRouteHrefListener;
use FiveLab\Bundle\ResourceBundle\Resource\Href\SymfonyRouteHref;
use FiveLab\Component\Resource\Resource\Action\Action;
use FiveLab\Component\Resource\Resource\Action\ActionCollection;
use FiveLab\Component\Resource\Resource\Action\Method;
use FiveLab\Component\Resource\Resource\ActionedResourceInterface;
use FiveLab\Component\Resource\Resource\Href\Href;
use FiveLab\Component\Resource\Resource\RelatedResourceInterface;
use FiveLab\Component\Resource\Resource\Relation\Relation;
use FiveLab\Component\Resource\Resource\Relation\RelationCollection;
use FiveLab\Component\Resource\Resource\ResourceInterface;
use FiveLab\Component\Resource\Serializer\Events\BeforeNormalizationEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class GenerateSymfonyRouteHrefListenerTest extends TestCase
{
    /**
     * @var UrlGeneratorInterface|MockObject
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var GenerateSymfonyRouteHrefListener
     */
    private GenerateSymfonyRouteHrefListener $listener;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->listener = new GenerateSymfonyRouteHrefListener($this->urlGenerator);
    }

    /**
     * @test
     */
    public function shouldNotProcessIfPassNotRelatedAndActionedResource(): void
    {
        $resource = $this->createMock(ResourceInterface::class);

        $this->urlGenerator->expects(self::never())
            ->method('generate');

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));
    }

    /**
     * @test
     */
    public function shouldSuccessProcessWithRelations(): void
    {
        $resource = $this->createMock(RelatedResourceInterface::class);

        $relations = [
            new Relation('some', new Href('/path')),
            new Relation('about', new SymfonyRouteHref('route_name', ['route_params'], false, UrlGeneratorInterface::NETWORK_PATH)),
        ];

        $resource->expects(self::once())
            ->method('getRelations')
            ->willReturn(new RelationCollection(...$relations));

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('route_name', ['route_params'], UrlGeneratorInterface::NETWORK_PATH)
            ->willReturn('/symfony-path');

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));

        self::assertEquals(new Href('/symfony-path'), $relations[1]->getHref());
    }

    /**
     * @test
     */
    public function shouldSuccessProcessWithActions(): void
    {
        $resource = $this->createMock(ActionedResourceInterface::class);

        $actions = [
            new Action('some', new Href('/path'), Method::post()),
            new Action('about', new SymfonyRouteHref('route_name', ['route_params'], false, UrlGeneratorInterface::NETWORK_PATH), Method::post()),
        ];

        $resource->expects(self::once())
            ->method('getActions')
            ->willReturn(new ActionCollection(...$actions));

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with('route_name', ['route_params'], UrlGeneratorInterface::NETWORK_PATH)
            ->willReturn('/symfony-path');

        $this->listener->onBeforeNormalization(new BeforeNormalizationEvent($resource, 'json', []));

        self::assertEquals(new Href('/symfony-path'), $actions[1]->getHref());
    }
}
