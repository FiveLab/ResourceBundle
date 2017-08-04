<?php

/*
 * This file is part of the FiveLab ResourceBundle package
 *
 * (c) FiveLab
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace FiveLab\Bundle\ResourceBundle\Tests\DependencyInjection;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\ResourceExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ResourceExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessLoadWithoutConfig(): void
    {
        $container = new ContainerBuilder();

        $extension = new ResourceExtension();
        $extension->load([], $container);

        self::assertGreaterThan(0, $container->getServiceIds());
    }

    /**
     * @test
     */
    public function shouldSuccessGetAlias(): void
    {
        $extension = new ResourceExtension();

        self::assertEquals('fivelab_resource', $extension->getAlias());
    }
}
