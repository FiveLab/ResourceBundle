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
use FiveLab\Component\Resource\Serializers\Hateoas\HateoasSerializer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\PaginatedCollectionNormalizer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\RelationCollectionNormalizer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\RelationNormalizer;
use FiveLab\Component\Resource\Serializers\Hateoas\Normalizer\ResourceCollectionNormalizer;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class HateoasResoureExtensionTest extends AbstractExtensionTestCase
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
    public function shouldSuccessBuildHateoasSerializer(): void
    {
        $this->load([
            'serializers' => [
                'hateoas' => [
                    'enabled' => true,
                    'options' => [
                        'foo' => 'bar',
                        'bar' => 'foo',
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas.normalizer.relation', RelationNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas.normalizer.relation_collection', RelationCollectionNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas.normalizer.resource_collection', ResourceCollectionNormalizer::class);
        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas.normalizer.paginated_collection', PaginatedCollectionNormalizer::class);

        $this->assertContainerBuilderHasService('fivelab.serializer.hateoas', Serializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.hateoas', 0, [
            new Reference('fivelab.resource.serializer.hateoas.normalizer.paginated_collection'),
            new Reference('fivelab.resource.serializer.hateoas.normalizer.resource_collection'),
            new Reference('fivelab.resource.serializer.hateoas.normalizer.relation_collection'),
            new Reference('fivelab.resource.serializer.hateoas.normalizer.relation'),
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.hateoas', 1, []);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas_json', HateoasSerializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json', 0, new Reference('fivelab.serializer.hateoas'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json', 1, 'json');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json', 2, ['foo' => 'bar', 'bar' => 'foo']);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.hateoas_json', 'resource.serializer', [
            'supportable' => 'fivelab.resource.serializer.hateoas_json.supportable',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.hateoas_json.supportable', AcceptFormatSupportable::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json.supportable', 0, ['application/hal+json']);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json.supportable', 1, []);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.hateoas_json.supportable', 2, [ErrorResourceInterface::class]);
    }

    /**
     * @test
     */
    public function shouldNotBuildVndErrorIfNotEnabled(): void
    {
        $this->load([
            'serializers' => [
                'hateoas' => [
                    'enabled' => false,
                ],
            ],
        ]);

        self::assertFalse($this->container->hasDefinition('fivelab.resource.serializer.hateoas_json'), 'Hateoas is disabled.');
    }
}
