<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Container\StringServiceContainerAdapter;
use Sif\Foundation\Exceptions\InvalidServiceIdentifierException;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class StringServiceContainerAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        CounterService::reset();
    }

    public function testAdapterMapsStringIdentifiers(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('example'),
                new ExampleService('adapter'),
            ),
        );

        $adapter = new StringServiceContainerAdapter(
            new DefinitionServiceContainer($registry),
        );

        self::assertTrue($adapter->has('example'));

        $service = $adapter->get('example');

        self::assertInstanceOf(ExampleService::class, $service);
        self::assertSame('adapter', $service->name);
    }

    public function testAdapterPreservesLazyBehavior(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier('counter'),
                CounterService::class,
                ServiceLifetime::Singleton,
            ),
        );

        $adapter = new StringServiceContainerAdapter(
            new DefinitionServiceContainer($registry),
        );

        $reference = $adapter->lazy('counter');

        self::assertFalse($reference->isResolved());
        self::assertSame(0, CounterService::constructed());

        $reference->resolve();

        self::assertTrue($reference->isResolved());
        self::assertSame(1, CounterService::constructed());
    }

    public function testAdapterCreatesStringNamedScope(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier('counter'),
                CounterService::class,
                ServiceLifetime::Scoped,
            ),
        );

        $adapter = new StringServiceContainerAdapter(
            new DefinitionServiceContainer($registry),
        );
        $scope = $adapter->beginScope('request-1');

        self::assertSame(
            $scope->get('counter'),
            $scope->get('counter'),
        );
        self::assertSame(1, CounterService::constructed());
    }

    public function testInvalidStringIdentifierUsesNativeValidation(): void
    {
        $adapter = new StringServiceContainerAdapter(
            new DefinitionServiceContainer(
                new ServiceDefinitionRegistry(),
            ),
        );

        $this->expectException(
            InvalidServiceIdentifierException::class,
        );

        $adapter->get(' ');
    }
}
