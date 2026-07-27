<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionKind;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Exceptions\InvalidServiceDefinitionException;
use Sif\Foundation\Exceptions\InvalidServiceIdentifierException;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;
use Sif\Foundation\Tests\Fixtures\Container\StubServiceContainer;

final class ServiceDefinitionTest extends TestCase
{
    public function testServiceIdentifierPreservesOpaqueValue(): void
    {
        $identifier = new ServiceIdentifier('logger.audit');

        self::assertSame('logger.audit', $identifier->value());
        self::assertSame('logger.audit', (string) $identifier);
        self::assertTrue(
            $identifier->equals(new ServiceIdentifier('logger.audit')),
        );
        self::assertFalse(
            $identifier->equals(new ServiceIdentifier('logger.runtime')),
        );
    }

    public function testServiceIdentifierRejectsEmptyValue(): void
    {
        $this->expectException(InvalidServiceIdentifierException::class);

        new ServiceIdentifier(' ');
    }

    public function testClassDefinitionPreservesTypeAndLifetime(): void
    {
        $definition = ServiceDefinition::forClass(
            identifier: new ServiceIdentifier('example'),
            className: ExampleService::class,
            lifetime: ServiceLifetime::Singleton,
        );

        self::assertSame(
            ServiceDefinitionKind::ClassType,
            $definition->kind(),
        );
        self::assertSame(
            ServiceLifetime::Singleton,
            $definition->lifetime(),
        );
        self::assertSame(
            ExampleService::class,
            $definition->className(),
        );
        self::assertFalse($definition->isAlias());
    }

    public function testFactoryDefinitionStoresClosure(): void
    {
        $definition = ServiceDefinition::forFactory(
            identifier: new ServiceIdentifier('example.factory'),
            factory: static fn (
                \Sif\Foundation\Contracts\ServiceContainerInterface $container,
            ): object => new ExampleService(),
            lifetime: ServiceLifetime::Transient,
        );

        $factory = $definition->factory();

        self::assertNotNull($factory);
        self::assertSame(
            ServiceDefinitionKind::Factory,
            $definition->kind(),
        );
        self::assertSame(
            ServiceLifetime::Transient,
            $definition->lifetime(),
        );
        self::assertInstanceOf(
            ExampleService::class,
            $factory(new StubServiceContainer()),
        );
    }

    public function testInstanceDefinitionIsAlwaysSingleton(): void
    {
        $instance = new ExampleService('shared');

        $definition = ServiceDefinition::forInstance(
            new ServiceIdentifier('example.instance'),
            $instance,
        );

        self::assertSame(
            ServiceDefinitionKind::Instance,
            $definition->kind(),
        );
        self::assertSame(
            ServiceLifetime::Singleton,
            $definition->lifetime(),
        );
        self::assertSame($instance, $definition->instance());
    }

    public function testAliasDefinitionHasNoLifetime(): void
    {
        $definition = ServiceDefinition::alias(
            new ServiceIdentifier('example.alias'),
            new ServiceIdentifier('example.target'),
        );

        self::assertTrue($definition->isAlias());
        self::assertSame(
            ServiceDefinitionKind::Alias,
            $definition->kind(),
        );
        self::assertNull($definition->lifetime());
        self::assertSame(
            'example.target',
            $definition->aliasTarget()?->value(),
        );
    }

    public function testAliasCannotTargetItself(): void
    {
        $identifier = new ServiceIdentifier('example');

        $this->expectException(InvalidServiceDefinitionException::class);

        ServiceDefinition::alias($identifier, $identifier);
    }
}
