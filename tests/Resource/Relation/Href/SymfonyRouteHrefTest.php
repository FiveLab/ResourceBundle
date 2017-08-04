<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\Resource\Relation\Href;

use FiveLab\Bundle\ResourceBundle\Resource\Relation\Href\SymfonyRouteHref;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class SymfonyRouteHrefTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessCreate(): void
    {
        $href = new SymfonyRouteHref('route_name', ['attr'], true, UrlGeneratorInterface::NETWORK_PATH);

        self::assertEquals('route_name', $href->getRouteName());
        self::assertEquals(['attr'], $href->getRouteParameters());
        self::assertTrue($href->isTemplated());
        self::assertEquals(UrlGeneratorInterface::NETWORK_PATH, $href->getReferenceType());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage The method FiveLab\Bundle\ResourceBundle\Resource\Relation\Href\SymfonyRouteHref::getPath does not support.
     */
    public function shouldFailGetPath(): void
    {
        $href = new SymfonyRouteHref('route_name');

        $href->getPath();
    }
}
