<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ConstructorArgumentBindings;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Exceptions\UnresolvableConstructorParameterException;
use Sif\Foundation\Tests\Fixtures\Container\AutowiredService;
use Sif\Foundation\Tests\Fixtures\Container\CounterService;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;
use Sif\Foundation\Tests\Fixtures\Container\RequiredDependencyService;
use Sif\Foundation\Tests\Fixtures\Container\UnionDependencyService;

final class ConstructorAutowiringTest extends TestCase
{
    public function testAutowiredClassResolvesRegisteredClassDependency(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier(ExampleService::class),
                new ExampleService('dependency'),
            ),
        );
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(RequiredDependencyService::class),
                RequiredDependencyService::class,
            ),
        );

        $service = (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(RequiredDependencyService::class),
        );

        self::assertInstanceOf(
            RequiredDependencyService::class,
            $service,
        );
        self::assertSame('dependency', $service->dependency->name);
    }

    public function testExplicitScalarBindingAndDefaultsAreApplied(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier(ExampleService::class),
                new ExampleService('dependency'),
            ),
        );
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(AutowiredService::class),
                AutowiredService::class,
                constructorBindings: new ConstructorArgumentBindings([
                    'name' => ConstructorArgumentBinding::value('worker'),
                ]),
            ),
        );

        $service = (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(AutowiredService::class),
        );

        self::assertInstanceOf(AutowiredService::class, $service);
        self::assertSame('worker', $service->name);
        self::assertSame(3, $service->retries);
        self::assertNull($service->optional);
    }

    public function testExplicitServiceBindingOverridesDeclaredTypeIdentifier(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('custom.dependency'),
                new ExampleService('custom'),
            ),
        );
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(RequiredDependencyService::class),
                RequiredDependencyService::class,
                constructorBindings: new ConstructorArgumentBindings([
                    'dependency' => ConstructorArgumentBinding::service(
                        new ServiceIdentifier('custom.dependency'),
                    ),
                ]),
            ),
        );

        $service = (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(RequiredDependencyService::class),
        );

        self::assertInstanceOf(
            RequiredDependencyService::class,
            $service,
        );
        self::assertSame('custom', $service->dependency->name);
    }

    public function testBuiltinParameterWithoutBindingFails(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier(ExampleService::class),
                new ExampleService(),
            ),
        );
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(AutowiredService::class),
                AutowiredService::class,
            ),
        );

        try {
            (new DefinitionServiceContainer($registry))->get(
                new ServiceIdentifier(AutowiredService::class),
            );

            self::fail('Expected constructor parameter failure.');
        } catch (UnresolvableConstructorParameterException $failure) {
            self::assertSame('name', $failure->parameterName());
        }
    }

    public function testUnionTypeRequiresExplicitBinding(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(UnionDependencyService::class),
                UnionDependencyService::class,
            ),
        );

        $this->expectException(
            UnresolvableConstructorParameterException::class,
        );

        (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(UnionDependencyService::class),
        );
    }

    public function testUnionTypeMayUseExplicitServiceBinding(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('union.dependency'),
                new CounterService(),
            ),
        );
        $registry->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(UnionDependencyService::class),
                UnionDependencyService::class,
                constructorBindings: new ConstructorArgumentBindings([
                    'dependency' => ConstructorArgumentBinding::service(
                        new ServiceIdentifier('union.dependency'),
                    ),
                ]),
            ),
        );

        $service = (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(UnionDependencyService::class),
        );

        self::assertInstanceOf(
            UnionDependencyService::class,
            $service,
        );
        self::assertInstanceOf(
            CounterService::class,
            $service->dependency,
        );
    }

    public function testAutowiringDisabledPreservesPreviousBehavior(): void
    {
        $registry = new ServiceDefinitionRegistry();
        $registry->register(
            ServiceDefinition::forClass(
                new ServiceIdentifier(RequiredDependencyService::class),
                RequiredDependencyService::class,
            ),
        );

        $this->expectException(
            \Sif\Foundation\Exceptions\UnresolvableServiceException::class,
        );

        (new DefinitionServiceContainer($registry))->get(
            new ServiceIdentifier(RequiredDependencyService::class),
        );
    }
}
