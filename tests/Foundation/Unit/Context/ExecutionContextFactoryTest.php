<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Context;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContextFactory;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;
use Sif\Foundation\Tests\Fixtures\Context\SequenceContextIdGenerator;

final class ExecutionContextFactoryTest extends TestCase
{
    public function testCreatesRootContextWithSharedContextAndCorrelationIdentity(): void
    {
        $instant = new DateTimeImmutable('2026-07-27T15:00:00+00:00');
        $factory = new ExecutionContextFactory(
            new SequenceContextIdGenerator(['ctx-root']),
            new FrozenClock($instant),
        );
        $attributes = new ContextAttributes(['request_id' => 'req-001']);

        $context = $factory->createRoot(
            attributes: $attributes,
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'runtime.run',
            source: 'cli',
            locale: 'es-AR',
            timezone: 'America/Argentina/Mendoza',
        );

        self::assertSame('ctx-root', $context->contextId()->value());
        self::assertSame($context->contextId(), $context->correlationId());
        self::assertSame($instant, $context->createdAt());
        self::assertSame($attributes, $context->attributes());
        self::assertNull($context->causationId());
        self::assertNull($context->parentContextId());
        self::assertSame('actor-001', $context->actorId());
        self::assertSame('tenant-001', $context->tenantId());
        self::assertSame('runtime.run', $context->operation());
        self::assertSame('cli', $context->source());
        self::assertSame('es-AR', $context->locale());
        self::assertSame('America/Argentina/Mendoza', $context->timezone());
    }

    public function testDerivesChildWithNewIdentityAndPreservedCorrelation(): void
    {
        $factory = new ExecutionContextFactory(
            new SequenceContextIdGenerator(['ctx-root', 'ctx-child']),
            new FrozenClock(new DateTimeImmutable('2026-07-27T15:00:00+00:00')),
        );
        $root = $factory->createRoot(operation: 'runtime.run');
        $cause = new ContextId('event-001');

        $child = $factory->derive(
            parent: $root,
            causationId: $cause,
            operation: 'audit.write',
        );

        self::assertSame('ctx-child', $child->contextId()->value());
        self::assertSame($root->correlationId(), $child->correlationId());
        self::assertSame($root->contextId(), $child->parentContextId());
        self::assertSame($cause, $child->causationId());
        self::assertSame('audit.write', $child->operation());
    }

    public function testDerivationInheritsStableMetadataAndOptionalOperationSource(): void
    {
        $factory = new ExecutionContextFactory(
            new SequenceContextIdGenerator(['ctx-root', 'ctx-child']),
            new FrozenClock(new DateTimeImmutable('2026-07-27T15:00:00+00:00')),
        );
        $root = $factory->createRoot(
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'runtime.run',
            source: 'cli',
            locale: 'es-AR',
            timezone: 'America/Argentina/Mendoza',
        );

        $child = $factory->derive($root);

        self::assertSame('actor-001', $child->actorId());
        self::assertSame('tenant-001', $child->tenantId());
        self::assertSame('runtime.run', $child->operation());
        self::assertSame('cli', $child->source());
        self::assertSame('es-AR', $child->locale());
        self::assertSame('America/Argentina/Mendoza', $child->timezone());
    }

    public function testDerivationMergesAttributesWithoutMutatingParent(): void
    {
        $factory = new ExecutionContextFactory(
            new SequenceContextIdGenerator(['ctx-root', 'ctx-child']),
            new FrozenClock(new DateTimeImmutable('2026-07-27T15:00:00+00:00')),
        );
        $root = $factory->createRoot(
            new ContextAttributes([
                'request_id' => 'req-001',
                'attempt' => 1,
            ]),
        );

        $child = $factory->derive(
            $root,
            new ContextAttributes([
                'attempt' => 2,
                'worker' => 'queue-01',
            ]),
        );

        self::assertSame(
            ['request_id' => 'req-001', 'attempt' => 1],
            $root->attributes()->all(),
        );
        self::assertSame(
            ['request_id' => 'req-001', 'attempt' => 2, 'worker' => 'queue-01'],
            $child->attributes()->all(),
        );
        self::assertNotSame($root->attributes(), $child->attributes());
    }

    public function testMergingEmptyAttributesReusesImmutableCollection(): void
    {
        $attributes = new ContextAttributes(['request_id' => 'req-001']);

        self::assertSame($attributes, $attributes->merged(ContextAttributes::empty()));
    }

    public function testFactoryUsesClockForEveryCreatedContext(): void
    {
        $instant = new DateTimeImmutable('2026-07-27T15:30:00+00:00');
        $factory = new ExecutionContextFactory(
            new SequenceContextIdGenerator(['ctx-root', 'ctx-child']),
            new FrozenClock($instant),
        );

        $root = $factory->createRoot();
        $child = $factory->derive($root);

        self::assertSame($instant, $root->createdAt());
        self::assertSame($instant, $child->createdAt());
    }

    public function testDeterministicGeneratorFailsWhenSequenceIsExhausted(): void
    {
        $generator = new SequenceContextIdGenerator(['ctx-root']);
        $generator->generate();

        $this->expectException(\LogicException::class);

        $generator->generate();
    }
}
