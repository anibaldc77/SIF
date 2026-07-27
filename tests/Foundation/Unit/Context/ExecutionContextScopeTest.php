<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Context;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextCarrier;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContextFactory;
use Sif\Foundation\Context\ExecutionContextScope;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;
use Sif\Foundation\Tests\Fixtures\Context\SequenceContextIdGenerator;

final class ExecutionContextScopeTest extends TestCase
{
    public function testRunsOperationWithExactCarriedContextAndReturnsValue(): void
    {
        $scope = $this->rootScope(['ctx-root']);
        $received = null;

        $result = $scope->run(
            static function (ExecutionContextInterface $context) use (&$received): string {
                $received = $context;

                return 'completed';
            },
        );

        self::assertSame('completed', $result);
        self::assertSame($scope->context(), $received);
    }

    public function testPropagatesOperationExceptionWithoutTranslation(): void
    {
        $scope = $this->rootScope(['ctx-root']);
        $failure = new RuntimeException('contextual operation failed');

        $this->expectExceptionObject($failure);

        $scope->run(static function () use ($failure): never {
            throw $failure;
        });
    }

    public function testDerivesChildScopeWithPreservedCorrelationAndExplicitLineage(): void
    {
        $scope = $this->rootScope(['ctx-root', 'ctx-child']);
        $cause = new ContextId('event-001');

        $child = $scope->derive(
            attributes: new ContextAttributes(['attempt' => 2]),
            causationId: $cause,
            operation: 'queue.consume',
            source: 'worker',
        );

        self::assertNotSame($scope, $child);
        self::assertSame('ctx-child', $child->context()->contextId()->value());
        self::assertSame($scope->context()->correlationId(), $child->context()->correlationId());
        self::assertSame($scope->context()->contextId(), $child->context()->parentContextId());
        self::assertSame($cause, $child->context()->causationId());
        self::assertSame('queue.consume', $child->context()->operation());
        self::assertSame('worker', $child->context()->source());
    }

    public function testDerivationDoesNotMutateParentScopeOrAttributes(): void
    {
        $scope = $this->rootScope(
            ['ctx-root', 'ctx-child'],
            new ContextAttributes(['request_id' => 'req-001', 'attempt' => 1]),
        );

        $child = $scope->derive(new ContextAttributes(['attempt' => 2]));

        self::assertSame(
            ['request_id' => 'req-001', 'attempt' => 1],
            $scope->context()->attributes()->all(),
        );
        self::assertSame(
            ['request_id' => 'req-001', 'attempt' => 2],
            $child->context()->attributes()->all(),
        );
    }

    public function testCarrierPreservesIdentityAndSupportsImmutableReplacement(): void
    {
        $scope = $this->rootScope(['ctx-root', 'ctx-child']);
        $carrier = $scope->carrier();
        $child = $scope->derive();

        self::assertSame($scope->context(), $carrier->context());
        self::assertSame($carrier, $carrier->withContext($scope->context()));

        $replacement = $carrier->withContext($child->context());

        self::assertNotSame($carrier, $replacement);
        self::assertSame($child->context(), $replacement->context());
        self::assertSame($scope->context(), $carrier->context());
    }

    public function testCreatesScopeFromExplicitCarrier(): void
    {
        $factory = $this->factory(['ctx-root']);
        $context = $factory->createRoot(operation: 'runtime.run');
        $carrier = new ContextCarrier($context);

        $scope = ExecutionContextScope::fromCarrier($carrier, $factory);

        self::assertSame($context, $scope->context());
        self::assertSame('runtime.run', $scope->context()->operation());
    }

    public function testNestedScopesDoNotLeakContextBetweenOperations(): void
    {
        $parent = $this->rootScope(['ctx-root', 'ctx-child']);
        $child = $parent->derive(operation: 'child.operation');
        $observed = [];

        $parent->run(static function (ExecutionContextInterface $context) use (&$observed): void {
            $observed[] = $context->contextId()->value();
        });
        $child->run(static function (ExecutionContextInterface $context) use (&$observed): void {
            $observed[] = $context->contextId()->value();
        });
        $parent->run(static function (ExecutionContextInterface $context) use (&$observed): void {
            $observed[] = $context->contextId()->value();
        });

        self::assertSame(['ctx-root', 'ctx-child', 'ctx-root'], $observed);
        self::assertSame('child.operation', $child->context()->operation());
        self::assertNotSame($parent->context(), $child->context());
    }

    /** @param non-empty-list<non-empty-string> $ids */
    private function rootScope(array $ids, ?ContextAttributes $attributes = null): ExecutionContextScope
    {
        $factory = $this->factory($ids);
        $context = $factory->createRoot(
            attributes: $attributes ?? ContextAttributes::empty(),
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'runtime.run',
            source: 'cli',
            locale: 'es-AR',
            timezone: 'America/Argentina/Buenos_Aires',
        );

        return new ExecutionContextScope($context, $factory);
    }

    /** @param non-empty-list<non-empty-string> $ids */
    private function factory(array $ids): ExecutionContextFactory
    {
        return new ExecutionContextFactory(
            new SequenceContextIdGenerator($ids),
            new FrozenClock(new DateTimeImmutable('2026-07-27T16:00:00+00:00')),
        );
    }
}
