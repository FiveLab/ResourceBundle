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

namespace FiveLab\Bundle\ResourceBundle\Tests\DependencyInjection;

use FiveLab\Bundle\ResourceBundle\DependencyInjection\ResourceExtension;
use FiveLab\Component\Resource\Resource\Error\ErrorResourceInterface;
use FiveLab\Component\Resource\Serializer\Resolver\AcceptFormatSupportable;
use FiveLab\Component\Resource\Serializer\Serializer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\RelationCollectionNormalizer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\RelationNormalizer;
use FiveLab\Component\Resource\Serializers\VndError\Normalizer\ErrorCollectionNormalizer;
use FiveLab\Component\Resource\Serializers\VndError\Normalizer\ErrorResourceNormalizer;
use FiveLab\Component\Resource\Serializers\VndError\VndErrorSerializer;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class VndErrorResoureExtensionTest extends AbstractExtensionTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        return [
            new ResourceExtension(),
        ];
    }

    /**
     * @test
     */
    public function shouldSuccessBuildVndErrorSerializer(): void
    {
        $this->load([
            'serializers' => [
                'vnd_error' => [
                    'enabled' => true,
                    'options' => [
                        'foo' => 'bar',
                        'bar' => 'foo',
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error.normalizer.error_resource', ErrorResourceNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error.normalizer.error_collection', ErrorCollectionNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error.normalizer.relation', RelationNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error.normalizer.relation_collection', RelationCollectionNormalizer::class);

        $this->assertContainerBuilderHasService('fivelab.serializer.vnd_error', Serializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.vnd_error', 0, [
            new Reference('fivelab.resource.serializer.vnd_error.normalizer.relation'),
            new Reference('fivelab.resource.serializer.vnd_error.normalizer.relation_collection'),
            new Reference('fivelab.resource.serializer.vnd_error.normalizer.error_collection'),
            new Reference('fivelab.resource.serializer.vnd_error.normalizer.error_resource'),
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.vnd_error', 1, []);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error_json', VndErrorSerializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.vnd_error_json', 0, new Reference('fivelab.serializer.vnd_error'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.vnd_error_json', 1, 'json');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.vnd_error_json', 2, ['foo' => 'bar', 'bar' => 'foo']);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.vnd_error_json', 'resource.serializer', [
            'supportable' => 'fivelab.resource.serializer.vnd_error_json.supportable',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.vnd_error_json.supportable', AcceptFormatSupportable::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.vnd_error_json.supportable', 0, ['application/vnd.error+json']);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.vnd_error_json.supportable', 1, [ErrorResourceInterface::class]);
    }

    /**
     * @test
     */
    public function shouldNotBuildVndErrorIfNotEnabled(): void
    {
        $this->load([
            'serializers' => [
                'vnd_error' => [
                    'enabled' => false,
                ],
            ],
        ]);

        self::assertFalse($this->container->hasDefinition('fivelab.resource.serializer.vnd_error_json'), 'Vnd.Error is disabled.');
    }
}
