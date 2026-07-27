<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ScopeIdentifier;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;

final class LazyServiceReferenceTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testLazyReferenceDoesNotCreateServiceUntilResolved(): void
    {
        $identifier = new ServiceIdentifier('counter');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                CounterService::class,
                ServiceLifetime::Singleton,
            ),
        );

        $reference = (new DefinitionServiceContainer($registry))
            ->lazy($identifier);

        self::assertFalse($reference->isResolved());
        self::assertSame(0, CounterService::constructed());

        $service = $reference->resolve();

        self::assertInstanceOf(CounterService::class, $service);
        self::assertTrue($reference->isResolved());
        self::assertSame(1, CounterService::constructed());
        self::assertSame($service, $reference->resolve());
    }

    public function testScopedLazyReferencePreservesCurrentScope(): void
    {
        $identifier = new ServiceIdentifier('scoped.counter');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                CounterService::class,
                ServiceLifetime::Scoped,
            ),
        );

        $scope = (new DefinitionServiceContainer($registry))
            ->beginScope(new ScopeIdentifier('lazy-scope'));
        $reference = $scope->lazy($identifier);

        self::assertSame(
            $scope->get($identifier),
            $reference->resolve(),
        );
    }
}
