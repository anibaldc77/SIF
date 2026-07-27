<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Exceptions\CircularServiceDependencyException;
use Sif\Foundation\Exceptions\ServiceCreationException;
use Sif\Foundation\Exceptions\ServiceDefinitionNotFoundException;
use Sif\Foundation\Exceptions\MissingActiveServiceScopeException;
use Sif\Foundation\Exceptions\UnresolvableServiceException;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;
use Sif\Foundation\Tests\Fixtures\Container\RequiredDependencyService;

final class DefinitionServiceContainerTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testExistingInstancePreservesIdentity(): void
    {
        $instance = new ExampleService('shared');
        $identifier = new ServiceIdentifier('example');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance($identifier, $instance),
        );

        $container = new DefinitionServiceContainer($registry);

        self::assertTrue($container->has($identifier));
        self::assertSame($instance, $container->get($identifier));
        self::assertSame($instance, $container->get($identifier));
    }

    public function testTransientClassProducesNewInstanceEachTime(): void
    {
        $identifier = new ServiceIdentifier('counter');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                CounterService::class,
                ServiceLifetime::Transient,
            ),
        );

        $container = new DefinitionServiceContainer($registry);
        $first = $container->get($identifier);
        $second = $container->get($identifier);

        self::assertNotSame($first, $second);
        self::assertSame(2, CounterService::constructed());
    }

    public function testSingletonClassProducesOneInstance(): void
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

        $container = new DefinitionServiceContainer($registry);

        self::assertSame(
            $container->get($identifier),
            $container->get($identifier),
        );
        self::assertSame(1, CounterService::constructed());
    }

    public function testFactoryMayResolveAnotherService(): void
    {
        $dependencyId = new ServiceIdentifier('dependency');
        $serviceId = new ServiceIdentifier('service');
        $registry = new ServiceDefinitionRegistry();

        $registry->register(
            ServiceDefinition::forInstance(
                $dependencyId,
                new ExampleService('dependency'),
            ),
        );
        $registry->register(
            ServiceDefinition::forFactory(
                $serviceId,
                static function (
                    \Sif\Foundation\Contracts\ServiceContainerInterface $container,
                ): object {
                    $dependency = $container->get(
                        new ServiceIdentifier('dependency'),
                    );

                    if (!$dependency instanceof ExampleService) {
                        throw new \LogicException(
                            'Resolved dependency has an unexpected type.',
                        );
                    }

                    return new RequiredDependencyService($dependency);
                },
            ),
        );

        $service = (new DefinitionServiceContainer($registry))
            ->get($serviceId);

        self::assertInstanceOf(
            RequiredDependencyService::class,
            $service,
        );
        self::assertSame('dependency', $service->dependency->name);
    }

    public function testAliasPreservesSingletonTargetIdentity(): void
    {
        $target = new ServiceIdentifier('target');
        $alias = new ServiceIdentifier('alias');
        $registry = new ServiceDefinitionRegistry();

        $registry->register(
            ServiceDefinition::forClass(
                $target,
                CounterService::class,
                ServiceLifetime::Singleton,
            ),
        );
        $registry->register(
            ServiceDefinition::alias($alias, $target),
        );

        $container = new DefinitionServiceContainer($registry);

        self::assertSame(
            $container->get($target),
            $container->get($alias),
        );
        self::assertSame(1, CounterService::constructed());
    }

    public function testAliasChainResolvesTerminalDefinition(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('first'),
                new ServiceIdentifier('second'),
            ),
        );
        $registry->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('second'),
                new ServiceIdentifier('third'),
            ),
        );
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('third'),
                new ExampleService('terminal'),
            ),
        );

        $service = (new DefinitionServiceContainer($registry))
            ->get(new ServiceIdentifier('first'));

        self::assertInstanceOf(ExampleService::class, $service);
        self::assertSame('terminal', $service->name);
    }

    public function testDirectAliasCycleIsDetected(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('first'),
                new ServiceIdentifier('second'),
            ),
        );
        $registry->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('second'),
                new ServiceIdentifier('first'),
            ),
        );

        try {
            (new DefinitionServiceContainer($registry))
                ->get(new ServiceIdentifier('first'));

            self::fail('Expected circular dependency exception.');
        } catch (CircularServiceDependencyException $failure) {
            self::assertSame(
                'first -> second -> first',
                $failure->path()->format(),
            );
        }
    }

    public function testFactoryCycleIsDetected(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forFactory(
                new ServiceIdentifier('first'),
                static fn (
                    \Sif\Foundation\Contracts\ServiceContainerInterface $container,
                ): object => $container->get(
                    new ServiceIdentifier('second'),
                ),
            ),
        );
        $registry->register(
            ServiceDefinition::forFactory(
                new ServiceIdentifier('second'),
                static fn (
                    \Sif\Foundation\Contracts\ServiceContainerInterface $container,
                ): object => $container->get(
                    new ServiceIdentifier('first'),
                ),
            ),
        );

        $this->expectException(
            CircularServiceDependencyException::class,
        );

        (new DefinitionServiceContainer($registry))
            ->get(new ServiceIdentifier('first'));
    }

    public function testClassWithRequiredArgumentsFailsWithoutAutowiring(): void
    {
        $identifier = new ServiceIdentifier('required');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                RequiredDependencyService::class,
            ),
        );

        $this->expectException(UnresolvableServiceException::class);

        (new DefinitionServiceContainer($registry))->get($identifier);
    }

    public function testScopedLifetimeRequiresActiveScope(): void
    {
        $identifier = new ServiceIdentifier('scoped');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $identifier,
                ExampleService::class,
                ServiceLifetime::Scoped,
            ),
        );

        try {
            (new DefinitionServiceContainer($registry))->get($identifier);

            self::fail('Expected missing active scope exception.');
        } catch (MissingActiveServiceScopeException $failure) {
            self::assertSame(
                'scoped',
                $failure->requestedIdentifier()->value(),
            );
            self::assertSame(
                'scoped',
                $failure->path()->format(),
            );
        }
    }

    public function testFactoryFailurePreservesOriginalCause(): void
    {
        $cause = new RuntimeException('factory failed');
        $identifier = new ServiceIdentifier('factory');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forFactory(
                $identifier,
                static function () use ($cause): object {
                    throw $cause;
                },
            ),
        );

        try {
            (new DefinitionServiceContainer($registry))->get($identifier);

            self::fail('Expected service creation exception.');
        } catch (ServiceCreationException $failure) {
            self::assertSame($cause, $failure->cause());
            self::assertSame($cause, $failure->getPrevious());
            self::assertSame(
                'factory',
                $failure->requestedIdentifier()->value(),
            );
        }
    }

    public function testUnknownIdentifierUsesRegistryFailure(): void
    {
        $this->expectException(
            ServiceDefinitionNotFoundException::class,
        );

        (new DefinitionServiceContainer(
            new ServiceDefinitionRegistry(),
        ))->get(new ServiceIdentifier('missing'));
    }

    public function testForgetRemovesTerminalSingletonThroughAlias(): void
    {
        $target = new ServiceIdentifier('target');
        $alias = new ServiceIdentifier('alias');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                $target,
                CounterService::class,
                ServiceLifetime::Singleton,
            ),
        );
        $registry->register(
            ServiceDefinition::alias($alias, $target),
        );

        $container = new DefinitionServiceContainer($registry);
        $first = $container->get($alias);

        $container->forget($alias);

        $second = $container->get($target);

        self::assertNotSame($first, $second);
        self::assertSame(2, CounterService::constructed());
    }

    public function testResolutionPathIsEmptyAfterSuccessfulResolution(): void
    {
        $identifier = new ServiceIdentifier('example');
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                $identifier,
                new ExampleService(),
            ),
        );

        $container = new DefinitionServiceContainer($registry);
        $container->get($identifier);

        self::assertTrue($container->resolutionPath()->isEmpty());
    }
}
