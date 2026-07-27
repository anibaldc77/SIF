<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Context;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Exceptions\InvalidContextAttributeKeyException;
use Sif\Foundation\Exceptions\InvalidContextIdException;
use Sif\Foundation\Exceptions\UnsupportedContextAttributeValueException;

final class ExecutionContextTest extends TestCase
{
    public function testContextIdPreservesOpaqueValueAndSupportsEquality(): void
    {
        $first = new ContextId('ctx-001');
        $same = new ContextId('ctx-001');
        $other = new ContextId('ctx-002');

        self::assertSame('ctx-001', $first->value());
        self::assertSame('ctx-001', (string) $first);
        self::assertTrue($first->equals($same));
        self::assertFalse($first->equals($other));
    }

    public function testContextIdRejectsEmptyValue(): void
    {
        $this->expectException(InvalidContextIdException::class);

        new ContextId('   ');
    }

    public function testAttributesAcceptNestedDeterministicValues(): void
    {
        $values = [
            'attempt' => 3,
            'enabled' => true,
            'metadata' => [
                'labels' => ['runtime', 'audit'],
                'nullable' => null,
                'ratio' => 1.5,
            ],
        ];
        $attributes = new ContextAttributes($values);

        self::assertFalse($attributes->isEmpty());
        self::assertSame(3, $attributes->count());
        self::assertTrue($attributes->has('metadata'));
        self::assertSame($values, $attributes->all());
        self::assertSame(3, $attributes->get('attempt'));
        self::assertNull($attributes->get('missing'));
    }

    public function testEmptyAttributesAreExplicit(): void
    {
        $attributes = ContextAttributes::empty();

        self::assertTrue($attributes->isEmpty());
        self::assertSame(0, $attributes->count());
        self::assertSame([], $attributes->all());
    }

    public function testAttributesRejectEmptyTopLevelKey(): void
    {
        $this->expectException(InvalidContextAttributeKeyException::class);

        new ContextAttributes(['' => 'value']);
    }

    public function testAttributesRejectEmptyNestedAssociativeKey(): void
    {
        $this->expectException(InvalidContextAttributeKeyException::class);

        new ContextAttributes(['metadata' => [' ' => 'value']]);
    }

    public function testAttributesRejectObjects(): void
    {
        $this->expectException(UnsupportedContextAttributeValueException::class);

        new ContextAttributes(['object' => new \stdClass()]);
    }

    public function testAttributesRejectNonFiniteFloat(): void
    {
        $this->expectException(UnsupportedContextAttributeValueException::class);

        new ContextAttributes(['invalid' => INF]);
    }

    public function testAttributesRejectRecursiveArrays(): void
    {
        $recursive = [];
        $recursive['self'] = &$recursive;

        $this->expectException(UnsupportedContextAttributeValueException::class);

        new ContextAttributes(['recursive' => $recursive]);
    }

    public function testExecutionContextExposesAllImmutableValuesThroughContract(): void
    {
        $contextId = new ContextId('ctx-001');
        $correlationId = new ContextId('corr-001');
        $causationId = new ContextId('cause-001');
        $parentId = new ContextId('parent-001');
        $createdAt = new DateTimeImmutable('2026-07-27T12:00:00+00:00');
        $attributes = new ContextAttributes(['request_id' => 'req-001']);

        $context = new ExecutionContext(
            contextId: $contextId,
            correlationId: $correlationId,
            createdAt: $createdAt,
            attributes: $attributes,
            causationId: $causationId,
            parentContextId: $parentId,
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'runtime.run',
            source: 'test',
            locale: 'es-AR',
            timezone: 'America/Argentina/Mendoza',
        );

        self::assertInstanceOf(ExecutionContextInterface::class, $context);
        self::assertSame($contextId, $context->contextId());
        self::assertSame($correlationId, $context->correlationId());
        self::assertSame($causationId, $context->causationId());
        self::assertSame($parentId, $context->parentContextId());
        self::assertSame('actor-001', $context->actorId());
        self::assertSame('tenant-001', $context->tenantId());
        self::assertSame('runtime.run', $context->operation());
        self::assertSame('test', $context->source());
        self::assertSame('es-AR', $context->locale());
        self::assertSame('America/Argentina/Mendoza', $context->timezone());
        self::assertSame($createdAt, $context->createdAt());
        self::assertSame($attributes, $context->attributes());
    }

    public function testExecutionContextSupportsAbsentOptionalValues(): void
    {
        $context = new ExecutionContext(
            new ContextId('ctx-001'),
            new ContextId('ctx-001'),
            new DateTimeImmutable('2026-07-27T12:00:00+00:00'),
        );

        self::assertNull($context->causationId());
        self::assertNull($context->parentContextId());
        self::assertNull($context->actorId());
        self::assertNull($context->tenantId());
        self::assertNull($context->operation());
        self::assertNull($context->source());
        self::assertNull($context->locale());
        self::assertNull($context->timezone());
        self::assertTrue($context->attributes()->isEmpty());
    }
}
