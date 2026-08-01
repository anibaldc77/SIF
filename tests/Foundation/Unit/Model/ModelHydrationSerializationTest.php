<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Model\Exceptions\InvalidModelAttributeValueException;
use Sif\Foundation\Model\Exceptions\ModelHydrationException;
use Sif\Foundation\Model\Metadata\ModelAttributeCast;
use Sif\Foundation\Model\Metadata\ModelAttributeDefinition;
use Sif\Foundation\Model\Metadata\ModelAttributeName;
use Sif\Foundation\Model\Metadata\ModelIdentityDefinition;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\State\ModelHydrator;
use Sif\Foundation\Model\State\ModelSerializer;

final class ModelHydrationSerializationTest extends TestCase
{
    public function testHydrationAppliesDeclaredCastsAndCreatesCleanSnapshot(): void
    {
        $state = (new ModelHydrator())->hydrate(self::metadata(), [
            'id' => '7',
            'name' => 123,
            'settings' => '{"enabled":true}',
            'created_at' => '2026-07-31T20:00:00-03:00',
        ]);

        self::assertSame(7, $state->get('id'));
        self::assertSame('123', $state->get('name'));
        self::assertSame(['enabled' => true], $state->get('settings'));
        self::assertInstanceOf(DateTimeImmutable::class, $state->get('created_at'));
        self::assertFalse($state->isDirty());
    }

    public function testMassAssignmentAcceptsOnlyFillableAttributes(): void
    {
        $state = (new ModelHydrator())->hydrate(self::metadata(), ['id' => 1, 'name' => 'Before']);
        $state->fill(['name' => 'After']);

        self::assertSame('After', $state->get('name'));
        self::assertTrue($state->isDirty('name'));

        $this->expectException(ModelHydrationException::class);
        $state->fill(['id' => 2]);
    }

    public function testReadOnlyAttributeCannotBeSetDirectly(): void
    {
        $state = (new ModelHydrator())->hydrate(self::metadata(), ['id' => 1]);

        $this->expectException(ModelHydrationException::class);
        $state->set('id', 2);
    }

    public function testDirtyTrackingReturnsOnlyChangedValuesAndCanBeSynchronized(): void
    {
        $state = (new ModelHydrator())->hydrate(self::metadata(), ['id' => 1, 'name' => 'Before']);
        $state->set('name', 'After');

        self::assertSame(['name' => 'After'], $state->dirty());
        self::assertSame('Before', $state->original()['name']);

        $state->syncOriginal();
        self::assertFalse($state->isDirty());
    }

    public function testPublicSerializationOmitsHiddenAttributesAndNormalizesDates(): void
    {
        $metadata = self::metadata();
        $state = (new ModelHydrator())->hydrate($metadata, [
            'id' => 1,
            'name' => 'User',
            'secret' => 'token',
            'created_at' => '2026-07-31T20:00:00-03:00',
        ]);

        $serializer = new ModelSerializer();
        $public = $serializer->publicArray($metadata, $state);
        $storage = $serializer->storageArray($metadata, $state);

        self::assertArrayNotHasKey('secret', $public);
        self::assertSame('token', $storage['secret']);
        self::assertSame('2026-07-31T20:00:00-03:00', $public['created_at']);
    }

    public function testUnknownHydrationAttributeIsRejected(): void
    {
        $this->expectException(ModelHydrationException::class);
        (new ModelHydrator())->hydrate(self::metadata(), ['unknown' => 'value']);
    }

    public function testInvalidJsonIsRejected(): void
    {
        $this->expectException(InvalidModelAttributeValueException::class);
        (new ModelHydrator())->hydrate(self::metadata(), ['settings' => '{invalid']);
    }

    private static function metadata(): ModelMetadata
    {
        return new ModelMetadata(
            HydratedTestModel::class,
            'hydrated_models',
            [
                new ModelAttributeDefinition(new ModelAttributeName('id'), ModelAttributeCast::Integer, false, readOnly: true),
                new ModelAttributeDefinition(new ModelAttributeName('name'), ModelAttributeCast::String, false, fillable: true),
                new ModelAttributeDefinition(new ModelAttributeName('settings'), ModelAttributeCast::Json, true, fillable: true),
                new ModelAttributeDefinition(new ModelAttributeName('secret'), ModelAttributeCast::String, true, hidden: true),
                new ModelAttributeDefinition(new ModelAttributeName('created_at'), ModelAttributeCast::ImmutableDateTime, true, readOnly: true),
            ],
            new ModelIdentityDefinition([new ModelAttributeName('id')]),
            createdAt: new ModelAttributeName('created_at'),
        );
    }
}

final class HydratedTestModel
{
}
