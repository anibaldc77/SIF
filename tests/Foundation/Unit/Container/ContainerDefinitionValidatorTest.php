<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Container;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Container\ConstructorArgumentBinding;
use Sif\Foundation\Container\ContainerDefinitionValidator;
use Sif\Foundation\Container\ContainerDiagnosticCode;
use Sif\Foundation\Container\ContextualBinding;
use Sif\Foundation\Container\ContextualBindingRegistry;
use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceDefinitionRegistry;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Tests\Fixtures\Container\ExampleService;

final class ContainerDefinitionValidatorTest extends TestCase
{
    public function testValidDefinitionsProduceEmptyReport(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::forInstance(
                new ServiceIdentifier('example'),
                new ExampleService(),
            ),
        );

        $report = (new ContainerDefinitionValidator(
            $definitions,
            new ContextualBindingRegistry(),
        ))->validate();

        self::assertTrue($report->isValid());
        self::assertSame(0, $report->count());
    }

    public function testMissingAliasTargetProducesDiagnostic(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $definitions->register(
            ServiceDefinition::alias(
                new ServiceIdentifier('alias'),
                new ServiceIdentifier('missing'),
            ),
        );

        $report = (new ContainerDefinitionValidator(
            $definitions,
            new ContextualBindingRegistry(),
        ))->validate();

        self::assertFalse($report->isValid());
        self::assertSame(
            ContainerDiagnosticCode::AliasTargetMissing,
            $report->diagnostics()[0]->code(),
        );
    }

    public function testContextualBindingValidationIsDeterministic(): void
    {
        $definitions = new ServiceDefinitionRegistry();
        $bindings = new ContextualBindingRegistry();
        $bindings->register(
            new ContextualBinding(
                consumer: new ServiceIdentifier('missing.consumer'),
                parameterName: 'dependency',
                binding: ConstructorArgumentBinding::service(
                    new ServiceIdentifier('missing.service'),
                ),
            ),
        );

        $report = (new ContainerDefinitionValidator(
            $definitions,
            $bindings,
        ))->validate();

        self::assertSame(2, $report->count());
        self::assertSame(
            [
                ContainerDiagnosticCode::ContextualConsumerMissing,
                ContainerDiagnosticCode::ContextualServiceMissing,
            ],
            array_map(
                static fn ($diagnostic) => $diagnostic->code(),
                $report->diagnostics(),
            ),
        );
    }
}
