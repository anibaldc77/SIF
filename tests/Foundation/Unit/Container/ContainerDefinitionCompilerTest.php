<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ConstructorArgumentBindings;
use Sif\Foundation\Container\ContainerDefinitionCompiler;
use Sif\Foundation\Container\ContainerDefinitionValidator;
use Sif\Foundation\Container\ContextualBindingRegistry;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\ServiceLifetime;
use Sif\Foundation\Container\ServiceTag;
use Sif\Foundation\Exceptions\ContainerCompilationException;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ContainerDefinitionCompilerTest extends TestCase
{
    public function testCompilationIsDeterministic(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::forAutowiredClass(
                identifier: new ServiceIdentifier('example'),
                className: ExampleService::class,
                lifetime: ServiceLifetime::Singleton,
                constructorBindings: new ConstructorArgumentBindings([
                    'name' => ConstructorArgumentBinding::value('compiled'),
                ]),
                tags: [
                    new ServiceTag(
                        name: 'example',
                        priority: 10,
                        metadata: ['format' => 'json'],
                    ),
                ],
            ),
        );

        $contextual = new ContextualBindingRegistry();
        $compiler = new ContainerDefinitionCompiler(
            definitions: $definitions,
            contextualBindings: $contextual,
            validator: new ContainerDefinitionValidator(
                $definitions,
                $contextual,
            ),
        );

        $first = $compiler->compile();
        $second = $compiler->compile();

        self::assertSame(
            $first->fingerprint(),
            $second->fingerprint(),
        );
        self::assertSame($first->toArray(), $second->toArray());
        self::assertSame(
            'example',
            $first->services()[0]->identifier(),
        );
    }

    public function testDifferentDefinitionChangesFingerprint(): void
    {
        $first = $this->compileNamed('first');
        $second = $this->compileNamed('second');

        self::assertNotSame(
            $first->fingerprint(),
            $second->fingerprint(),
        );
    }

    public function testInvalidDefinitionsCannotCompile(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('alias'),
                new ServiceIdentifier('missing'),
            ),
        );
        $contextual = new ContextualBindingRegistry();

        $this->expectException(ContainerCompilationException::class);

        (new ContainerDefinitionCompiler(
            definitions: $definitions,
            contextualBindings: $contextual,
            validator: new ContainerDefinitionValidator(
                $definitions,
                $contextual,
            ),
        ))->compile();
    }

    private function compileNamed(
        string $name,
    ): \Sif\Foundation\Container\CompiledContainerDefinition {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier($name),
                new ExampleService($name),
            ),
        );
        $contextual = new ContextualBindingRegistry();

        return (new ContainerDefinitionCompiler(
            definitions: $definitions,
            contextualBindings: $contextual,
            validator: new ContainerDefinitionValidator(
                $definitions,
                $contextual,
            ),
        ))->compile();
    }
}
