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
use Sif\Foundation\Exceptions\ClosedServiceScopeException;
use Sif\Foundation\Exceptions\InvalidScopeIdentifierException;
use Sif\Foundation\Exceptions\MissingActiveServiceScopeException;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ScopedConsumer;

final class ServiceScopeTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testScopeIdentifierRejectsEmptyValue(): void
    {
        $this->expectException(InvalidScopeIdentifierException::class);

        new ScopeIdentifier(' ');
    }

    public function testScopedServiceRequiresActiveScope(): void
    {
        $container = $this->container();

        $this->expectException(
            MissingActiveServiceScopeException::class,
        );

        $container->get(
            new ServiceIdentifier(CounterService::class),
        );
    }

    public function testScopedServicePreservesIdentityWithinScope(): void
    {
        $scope = $this->container()->beginScope(
            new ScopeIdentifier('request-1'),
        );
        $identifier = new ServiceIdentifier(CounterService::class);

        self::assertSame(
            $scope->get($identifier),
            $scope->get($identifier),
        );
        self::assertSame(1, CounterService::constructed());
    }

    public function testDifferentScopesReceiveDifferentInstances(): void
    {
        $container = $this->container();
        $identifier = new ServiceIdentifier(CounterService::class);

        $first = $container
            ->beginScope(new ScopeIdentifier('first'))
            ->get($identifier);
        $second = $container
            ->beginScope(new ScopeIdentifier('second'))
            ->get($identifier);

        self::assertNotSame($first, $second);
        self::assertSame(2, CounterService::constructed());
    }

    public function testNestedScopeIsIsolatedFromParent(): void
    {
        $container = $this->container();
        $identifier = new ServiceIdentifier(CounterService::class);
        $parent = $container->beginScope(
            new ScopeIdentifier('parent'),
        );
        $child = $parent->beginScope(
            new ScopeIdentifier('child'),
        );

        self::assertSame('parent', $child->parent()?->identifier()->value());
        self::assertNotSame(
            $parent->get($identifier),
            $child->get($identifier),
        );
    }

    public function testFactoryReceivesScopedContainer(): void
    {
        $registry = $this->registry();
        $registry->register(
            ServiceDefinition::forFactory(
                new ServiceIdentifier('consumer.factory'),
                static function (
                    \Sif\Foundation\Contracts\ServiceContainerInterface $container,
                ): object {
                    $dependency = $container->get(
                        new ServiceIdentifier(CounterService::class),
                    );

                    if (!$dependency instanceof CounterService) {
                        throw new \LogicException('Unexpected dependency.');
                    }

                    return new ScopedConsumer($dependency);
                },
                ServiceLifetime::Scoped,
            ),
        );

        $scope = (new DefinitionServiceContainer($registry))
            ->beginScope(new ScopeIdentifier('factory'));

        $first = $scope->get(
            new ServiceIdentifier('consumer.factory'),
        );
        $second = $scope->get(
            new ServiceIdentifier('consumer.factory'),
        );

        self::assertInstanceOf(ScopedConsumer::class, $first);
        self::assertSame($first, $second);
        self::assertSame(
            $first->dependency,
            $scope->get(new ServiceIdentifier(CounterService::class)),
        );
    }

    public function testAutowiredScopedDependencyUsesCurrentScope(): void
    {
        $registry = $this->registry();
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(ScopedConsumer::class),
                ScopedConsumer::class,
                ServiceLifetime::Scoped,
            ),
        );

        $scope = (new DefinitionServiceContainer($registry))
            ->beginScope(new ScopeIdentifier('autowire'));

        $consumer = $scope->get(
            new ServiceIdentifier(ScopedConsumer::class),
        );

        self::assertInstanceOf(ScopedConsumer::class, $consumer);
        self::assertSame(
            $consumer->dependency,
            $scope->get(new ServiceIdentifier(CounterService::class)),
        );
    }

    public function testClosingScopeClearsAndRejectsFurtherUse(): void
    {
        $scope = $this->container()->beginScope(
            new ScopeIdentifier('closed'),
        );
        $scope->get(new ServiceIdentifier(CounterService::class));
        $scope->close();

        self::assertTrue($scope->isClosed());

        $this->expectException(ClosedServiceScopeException::class);

        $scope->get(new ServiceIdentifier(CounterService::class));
    }

    public function testClosingParentInvalidatesChild(): void
    {
        $parent = $this->container()->beginScope(
            new ScopeIdentifier('parent'),
        );
        $child = $parent->beginScope(
            new ScopeIdentifier('child'),
        );

        $parent->close();

        $this->expectException(ClosedServiceScopeException::class);

        $child->get(new ServiceIdentifier(CounterService::class));
    }

    public function testForgetRemovesOnlyCurrentScopedInstance(): void
    {
        $scope = $this->container()->beginScope(
            new ScopeIdentifier('forget'),
        );
        $identifier = new ServiceIdentifier(CounterService::class);
        $first = $scope->get($identifier);

        $scope->forget($identifier);

        $second = $scope->get($identifier);

        self::assertNotSame($first, $second);
        self::assertSame(2, CounterService::constructed());
    }

    public function testSingletonRemainsSharedAcrossScopes(): void
    {
        $registry = $this->registry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier('singleton.counter'),
                CounterService::class,
                ServiceLifetime::Singleton,
            ),
        );

        $container = new DefinitionServiceContainer($registry);
        $first = $container
            ->beginScope(new ScopeIdentifier('first'))
            ->get(new ServiceIdentifier('singleton.counter'));
        $second = $container
            ->beginScope(new ScopeIdentifier('second'))
            ->get(new ServiceIdentifier('singleton.counter'));

        self::assertSame($first, $second);
        self::assertSame(1, CounterService::constructed());
    }

    private function container(): DefinitionServiceContainer
    {
        return new DefinitionServiceContainer($this->registry());
    }

    private function registry(): ServiceDefinitionRegistry
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier(CounterService::class),
                CounterService::class,
                ServiceLifetime::Scoped,
            ),
        );

        return $registry;
    }
}
