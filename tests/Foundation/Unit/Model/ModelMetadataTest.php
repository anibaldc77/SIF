<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Model\Exceptions\DuplicateModelMetadataException;
use Sif\Foundation\Model\Exceptions\InvalidModelAttributeDefinitionException;
use Sif\Foundation\Model\Exceptions\InvalidModelMetadataException;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\Metadata\ModelMetadataRegistry;

final class ModelMetadataTest extends TestCase
{
    public function testAttributeNamesAndCastsAreExplicit(): void
    {
        $definition = new ModelAttributeDefinition(
            new ModelAttributeName('display_name'),
            ModelAttributeCast::String,
            nullable: false,
            fillable: true,
        );

        self::assertSame('display_name', $definition->name()->value());
        self::assertSame(ModelAttributeCast::String, $definition->cast());
        self::assertFalse($definition->nullable());
        self::assertTrue($definition->fillable());
    }

    public function testFillableReadOnlyContradictionIsRejected(): void
    {
        $this->expectException(InvalidModelAttributeDefinitionException::class);

        new ModelAttributeDefinition(
            new ModelAttributeName('created_at'),
            fillable: true,
            readOnly: true,
        );
    }

    public function testCompositeIdentityPreservesOrder(): void
    {
        $identity = new ModelIdentityDefinition([
            new ModelAttributeName('tenant_id'),
            new ModelAttributeName('user_id'),
        ]);

        self::assertTrue($identity->composite());
        self::assertSame(['tenant_id', 'user_id'], $identity->names());
    }

    public function testMetadataExposesMassAssignmentAndVisibilityPolicies(): void
    {
        $metadata = self::metadata();

        self::assertSame(['name'], $metadata->fillableAttributes());
        self::assertSame(['secret'], $metadata->hiddenAttributes());
        self::assertTrue($metadata->usesSoftDeletes());
        self::assertSame('models', $metadata->repositoryName());
    }

    public function testUnknownIdentityAttributeIsRejected(): void
    {
        $this->expectException(InvalidModelMetadataException::class);

        new ModelMetadata(
            TestModel::class,
            'models',
            [new ModelAttributeDefinition(new ModelAttributeName('name'))],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
        );
    }

    public function testRegistryRejectsDuplicateModelMetadata(): void
    {
        $registry = new ModelMetadataRegistry([self::metadata()]);

        $this->expectException(DuplicateModelMetadataException::class);
        $registry->register(self::metadata());
    }

    public function testRegistryResolvesMetadataByModelClass(): void
    {
        $registry = new ModelMetadataRegistry([self::metadata()]);

        self::assertTrue($registry->has(TestModel::class));
        self::assertSame(TestModel::class, $registry->get(TestModel::class)->modelClass());
        self::assertSame(1, $registry->count());
    }

    private static function metadata(): ModelMetadata
    {
        return new ModelMetadata(
            TestModel::class,
            'models',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer, false, readOnly: true),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, false, fillable: true),
                new ModelAttributeDefinition(new ModelAttributeName('secret'), ModelAttributeCast::String, true, hidden: true),
                new ModelAttributeDefinition(new ModelAttributeName('deleted_at'), ModelAttributeCast::ImmutableDateTime),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
            deletedAt: new ModelAttributeName('deleted_at'),
        );
    }
}

final class TestModel
{
}
