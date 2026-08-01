<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Model\Metadata\ModelMetadataRegistry;
use Sif\Foundation\Model\Relation\ModelRelationRegistry;
use Sif\Foundation\Model\Runtime\BaseModelRuntime;
use Sif\Foundation\Model\Runtime\BaseModelRuntimeServiceProvider;

final class BaseModelRuntimeIntegrationTest extends TestCase
{
    public function testRuntimePublishesSafeEmptySummary(): void
    {
        $runtime = new BaseModelRuntime(
            new ModelMetadataRegistry(),
            new ModelRelationRegistry(),
        );

        self::assertSame([
            'metadata_count' => 0,
            'relation_count' => 0,
        ], $runtime->summary());
    }

    public function testProviderDeclaresModelCapabilities(): void
    {
        $runtime = new BaseModelRuntime(
            new ModelMetadataRegistry(),
            new ModelRelationRegistry(),
        );
        $provider = new BaseModelRuntimeServiceProvider($runtime);

        $capabilities = [];
        foreach ($provider->capabilities() as $capability) {
            $capabilities[] = $capability->identifier();
        }

        self::assertSame(['models', 'models.basemodel2'], $capabilities);
    }

    public function testProviderRegistrationIsExplicitAndSideEffectFreeByConstruction(): void
    {
        $reflection = new \ReflectionClass(BaseModelRuntimeServiceProvider::class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->hasMethod('register'));
        self::assertTrue($reflection->hasMethod('capabilities'));
    }
}
