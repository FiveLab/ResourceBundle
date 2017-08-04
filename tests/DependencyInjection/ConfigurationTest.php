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

use FiveLab\Bundle\ResourceBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSuccessGetTree(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();

        self::assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }
}
