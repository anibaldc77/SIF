<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ContainerCompositionFactory;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ContainerCompositionFactoryTest extends TestCase
{
    public function testFactoryWiresContainerValidatorAndCompiler(): void
    {
        $composition = (new ContainerCompositionFactory())->create();

        $composition->definitions()->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('example'),
                new ExampleService('composed'),
            ),
        );

        self::assertTrue(
            $composition->container()->has(
                new ServiceIdentifier('example'),
            ),
        );
        self::assertTrue($composition->validator()->validate()->isValid());
        self::assertNotSame(
            '',
            $composition->compiler()->compile()->fingerprint(),
        );
    }

    public function testCompatibilityAdapterUsesSameContainer(): void
    {
        $composition = (new ContainerCompositionFactory())->create();
        $instance = new ExampleService('shared');

        $composition->definitions()->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('example'),
                $instance,
            ),
        );

        self::assertSame(
            $composition->container()->get(
                new ServiceIdentifier('example'),
            ),
            $composition->compatibility()->get('example'),
        );
    }

    public function testFactoryAcceptsPreconfiguredRegistries(): void
    {
        $definitions = new \Sif\Foundation\Container\ServiceDefinitionRegistry();
        $contextual = new \Sif\Foundation\Container\ContextualBindingRegistry();

        $composition = (new ContainerCompositionFactory())->create(
            definitions: $definitions,
            contextualBindings: $contextual,
        );

        self::assertSame($definitions, $composition->definitions());
        self::assertSame(
            $contextual,
            $composition->contextualBindings(),
        );
    }
}
