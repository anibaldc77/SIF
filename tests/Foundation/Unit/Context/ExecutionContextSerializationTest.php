<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Context;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextDiagnosticSnapshot;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ContextRedactionPolicy;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Context\ExecutionContextSerializer;
use Sif\Foundation\Exceptions\InvalidContextRedactionPolicyException;

final class ExecutionContextSerializationTest extends TestCase
{
    public function testSerializesAllFieldsWithStableNamesAndTimestamp(): void
    {
        $context = $this->context();

        $serialized = (new ExecutionContextSerializer())->serialize($context);

        self::assertSame('ctx-002', $serialized['context_id']);
        self::assertSame('corr-001', $serialized['correlation_id']);
        self::assertSame('cause-001', $serialized['causation_id']);
        self::assertSame('parent-001', $serialized['parent_context_id']);
        self::assertSame('2026-07-27T15:30:45.123456+00:00', $serialized['created_at']);
        self::assertArrayHasKey('attributes', $serialized);
    }

    public function testRepresentsAbsentOptionalFieldsConsistentlyAsNull(): void
    {
        $context = new ExecutionContext(
            new ContextId('ctx-root'),
            new ContextId('ctx-root'),
            new DateTimeImmutable('2026-07-27T00:00:00+00:00'),
        );

        $serialized = (new ExecutionContextSerializer())->serialize($context);

        self::assertNull($serialized['causation_id']);
        self::assertNull($serialized['parent_context_id']);
        self::assertNull($serialized['actor_id']);
        self::assertNull($serialized['tenant_id']);
        self::assertNull($serialized['operation']);
        self::assertSame([], $serialized['attributes']);
    }

    public function testSortsAssociativeKeysRecursivelyAndPreservesListOrder(): void
    {
        $context = $this->context(new ContextAttributes([
            'zeta' => ['b' => 2, 'a' => 1],
            'alpha' => [3, 1, 2],
        ]));

        $attributes = (new ExecutionContextSerializer())->serialize($context)['attributes'];

        self::assertSame(['alpha', 'zeta'], array_keys($attributes));
        self::assertSame([3, 1, 2], $attributes['alpha']);
        self::assertSame(['a', 'b'], array_keys($attributes['zeta']));
    }

    public function testRedactsConfiguredKeysAtEveryAssociativeDepth(): void
    {
        $context = $this->context(new ContextAttributes([
            'token' => 'top-secret',
            'request' => [
                'authorization' => 'Bearer secret',
                'safe' => 'visible',
            ],
        ]));
        $policy = new ContextRedactionPolicy(['token', 'authorization']);

        $attributes = (new ExecutionContextSerializer())->serialize($context, $policy)['attributes'];

        self::assertSame('[REDACTED]', $attributes['token']);
        self::assertSame('[REDACTED]', $attributes['request']['authorization']);
        self::assertSame('visible', $attributes['request']['safe']);
    }

    public function testRedactionIsExactAndCaseSensitive(): void
    {
        $context = $this->context(new ContextAttributes([
            'token' => 'hidden',
            'Token' => 'visible',
        ]));

        $attributes = (new ExecutionContextSerializer())->serialize(
            $context,
            new ContextRedactionPolicy(['token']),
        )['attributes'];

        self::assertSame('[REDACTED]', $attributes['token']);
        self::assertSame('visible', $attributes['Token']);
    }

    public function testSupportsCustomStableMarker(): void
    {
        $context = $this->context(new ContextAttributes(['password' => 'secret']));

        $attributes = (new ExecutionContextSerializer())->serialize(
            $context,
            new ContextRedactionPolicy(['password'], '<hidden>'),
        )['attributes'];

        self::assertSame('<hidden>', $attributes['password']);
    }

    public function testRejectsEmptyRedactionKeys(): void
    {
        $this->expectException(InvalidContextRedactionPolicyException::class);

        new ContextRedactionPolicy(['  ']);
    }

    public function testRejectsEmptyRedactionMarker(): void
    {
        $this->expectException(InvalidContextRedactionPolicyException::class);

        new ContextRedactionPolicy([], '');
    }

    public function testDiagnosticSnapshotUsesSafeSerializedPayload(): void
    {
        $context = $this->context(new ContextAttributes(['access_token' => 'secret']));
        $snapshot = new ContextDiagnosticSnapshot(
            $context,
            new ExecutionContextSerializer(),
            new ContextRedactionPolicy(['access_token']),
        );

        self::assertSame('ctx-002', $snapshot->contextId());
        self::assertSame('corr-001', $snapshot->correlationId());
        self::assertSame('[REDACTED]', $snapshot->toArray()['attributes']['access_token']);
    }

    public function testSerializationDoesNotMutateSourceAttributes(): void
    {
        $attributes = new ContextAttributes(['secret' => 'original', 'safe' => 'value']);
        $context = $this->context($attributes);

        (new ExecutionContextSerializer())->serialize(
            $context,
            new ContextRedactionPolicy(['secret']),
        );

        self::assertSame('original', $attributes->get('secret'));
        self::assertSame('value', $attributes->get('safe'));
    }

    private function context(?ContextAttributes $attributes = null): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId('ctx-002'),
            correlationId: new ContextId('corr-001'),
            createdAt: new DateTimeImmutable('2026-07-27T15:30:45.123456+00:00'),
            attributes: $attributes ?? new ContextAttributes(['safe' => 'value']),
            causationId: new ContextId('cause-001'),
            parentContextId: new ContextId('parent-001'),
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'runtime.run',
            source: 'test',
            locale: 'es-AR',
            timezone: 'America/Argentina/Buenos_Aires',
        );
    }
}
