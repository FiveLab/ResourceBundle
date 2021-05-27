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
use FiveLab\Component\Resource\Serializer\Resolver\AcceptFormatSupportable;
use FiveLab\Component\Resource\Serializer\Serializer;
use FiveLab\Component\Resource\Serializers\WebApi\Normalizer\PaginatedCollectionNormalizer;
use FiveLab\Component\Resource\Serializers\WebApi\WebApiSerializer;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vitaliy Zhuk <v.zhuk@fivelab.org>
 */
class WebApiResourceExtensionTest extends AbstractExtensionTestCase
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
    public function shouldSuccessBuildWebApiSerializer(): void
    {
        $this->load([
            'serializers' => [
                'web_api' => [
                    'enabled' => true,
                    'options' => [
                        'foo' => 'bar',
                        'bar' => 'foo',
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.web_api_json.normalizer.paginated_collection', PaginatedCollectionNormalizer::class);

        $this->assertContainerBuilderHasService('fivelab.serializer.web_api', Serializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.web_api', 0, [
            'fivelab.resource.serializer.web_api_json.normalizer.paginated_collection',
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.serializer.web_api', 1, []);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.web_api_json', WebApiSerializer::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.web_api_json', 0, new Reference('fivelab.serializer.web_api'));
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.web_api_json', 1, 'json');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.web_api_json', 2, ['foo' => 'bar', 'bar' => 'foo']);
        $this->assertContainerBuilderHasServiceDefinitionWithTag('fivelab.resource.serializer.web_api_json', 'resource.serializer', [
            'supportable' => 'fivelab.resource.serializer.web_api_json.supportable',
        ]);

        $this->assertContainerBuilderHasService('fivelab.resource.serializer.web_api_json.supportable', AcceptFormatSupportable::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('fivelab.resource.serializer.web_api_json.supportable', 0, ['application/json']);
    }

    /**
     * @test
     */
    public function shouldNotBuildWebApiIfNotEnabled(): void
    {
        $this->load([
            'serializers' => [
                'web_api' => [
                    'enabled' => false,
                ],
            ],
        ]);

        self::assertFalse($this->container->hasDefinition('fivelab.resource.serializer.web_api_json'), 'WEB Api disabled.');
    }
}
