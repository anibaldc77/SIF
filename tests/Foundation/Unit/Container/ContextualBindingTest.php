<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ConstructorArgumentBindings;
use Sif\Foundation\Container\ContextualBinding;
use Sif\Foundation\Container\ContextualBindingRegistry;
use Sif\Foundation\Container\DefinitionServiceContainer;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Exceptions\DuplicateContextualBindingException;
use Sif\Foundation\Tests\Fixtures\Container\ContextualConsumer;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ContextualBindingTest extends TestCase
{
    public function testContextualBindingOverridesDirectTypeResolution(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier(ExampleService::class),
                new ExampleService('default'),
            ),
        );
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('contextual.example'),
                new ExampleService('contextual'),
            ),
        );
        $definitions->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(ContextualConsumer::class),
                ContextualConsumer::class,
            ),
        );

        $bindings = new ContextualBindingRegistry();
        $bindings->register(
            new ContextualBinding(
                consumer: new ServiceIdentifier(ContextualConsumer::class),
                parameterName: 'dependency',
                binding: ConstructorArgumentBinding::service(
                    new ServiceIdentifier('contextual.example'),
                ),
            ),
        );

        $service = (new DefinitionServiceContainer(
            $definitions,
            $bindings,
        ))->get(new ServiceIdentifier(ContextualConsumer::class));

        self::assertInstanceOf(ContextualConsumer::class, $service);
        self::assertSame('contextual', $service->dependency->name);
    }

    public function testExplicitDefinitionBindingOverridesContextualBinding(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('explicit.example'),
                new ExampleService('explicit'),
            ),
        );
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('contextual.example'),
                new ExampleService('contextual'),
            ),
        );
        $definitions->register(
            ServiceDefinition::forAutowiredClass(
                new ServiceIdentifier(ContextualConsumer::class),
                ContextualConsumer::class,
                constructorBindings: new ConstructorArgumentBindings([
                    'dependency' => ConstructorArgumentBinding::service(
                        new ServiceIdentifier('explicit.example'),
                    ),
                ]),
            ),
        );

        $bindings = new ContextualBindingRegistry();
        $bindings->register(
            new ContextualBinding(
                new ServiceIdentifier(ContextualConsumer::class),
                'dependency',
                ConstructorArgumentBinding::service(
                    new ServiceIdentifier('contextual.example'),
                ),
            ),
        );

        $service = (new DefinitionServiceContainer(
            $definitions,
            $bindings,
        ))->get(new ServiceIdentifier(ContextualConsumer::class));

        self::assertInstanceOf(ContextualConsumer::class, $service);
        self::assertSame('explicit', $service->dependency->name);
    }

    public function testDuplicateContextualBindingIsRejected(): void
    {
        $binding = new ContextualBinding(
            new ServiceIdentifier(ContextualConsumer::class),
            'dependency',
            ConstructorArgumentBinding::value(null),
        );

        $registry = new ContextualBindingRegistry();
        $registry->register($binding);

        $this->expectException(
            DuplicateContextualBindingException::class,
        );

        $registry->register($binding);
    }
}
