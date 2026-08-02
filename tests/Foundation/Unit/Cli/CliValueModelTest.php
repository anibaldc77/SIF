<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Exceptions\InvalidCliCommandNameException;
use Sif\Foundation\Cli\Exceptions\InvalidCliDefinitionException;
use Sif\Foundation\Cli\Exceptions\InvalidCliExitCodeException;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInteractionMode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Cli\Value\CliOptionDefinition;
use Sif\Foundation\Cli\Value\CliOptionName;
use Sif\Foundation\Cli\Value\CliVerbosity;

final class CliValueModelTest extends TestCase
{
    public function testCommandNameRequiresCanonicalNamespacedForm(): void
    {
        self::assertSame('migration:status', (new CliCommandName('migration:status'))->value());

        $this->expectException(InvalidCliCommandNameException::class);
        new CliCommandName('MigrationStatus');
    }

    public function testDefinitionsExposeSafeMetadata(): void
    {
        $argument = new CliArgumentDefinition(
            new CliArgumentName('target'),
            'Installation target.',
            required: true,
            sensitive: true,
        );
        $option = new CliOptionDefinition(
            new CliOptionName('--format'),
            'Output format.',
            shortcut: 'f',
            requiresValue: true,
        );

        self::assertTrue($argument->sensitive());
        self::assertSame('format', $option->name()->value());
        self::assertSame('f', $option->shortcut());
    }

    public function testMetadataPreservesDeterministicDefinitionOrder(): void
    {
        $metadata = new CliCommandMetadata(
            new CliCommandName('runtime:doctor'),
            'Diagnoses the runtime.',
            'Runs deterministic runtime checks.',
            [new CliArgumentDefinition(new CliArgumentName('scope'), 'Diagnostic scope.', true)],
            [new CliOptionDefinition(new CliOptionName('json'), 'Emit JSON output.')],
            CliOperationalClass::inspection(),
            interactiveAllowed: false,
            destructive: false,
            aliases: [new CliCommandName('runtime:diagnose')],
        );

        self::assertSame('scope', $metadata->arguments()[0]->name()->value());
        self::assertSame('json', $metadata->options()[0]->name()->value());
        self::assertSame('runtime:diagnose', $metadata->aliases()[0]->value());
    }

    public function testMetadataRejectsRequiredArgumentAfterOptionalOne(): void
    {
        $this->expectException(InvalidCliDefinitionException::class);

        new CliCommandMetadata(
            new CliCommandName('config:show'),
            'Shows configuration.',
            null,
            [
                new CliArgumentDefinition(new CliArgumentName('section'), 'Optional section.'),
                new CliArgumentDefinition(new CliArgumentName('key'), 'Required key.', true),
            ],
            [],
            CliOperationalClass::inspection(),
            true,
            false,
        );
    }

    public function testDestructiveMetadataRequiresMutatingClassification(): void
    {
        $this->expectException(InvalidCliDefinitionException::class);

        new CliCommandMetadata(
            new CliCommandName('migration:run'),
            'Runs migrations.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            true,
        );
    }

    public function testInvocationNormalizesOptionsWithoutExposingValuesInSummary(): void
    {
        $invocation = new CliInvocation(
            new CliCommandName('installer:run'),
            ['production'],
            [
                '--token' => ['secret-value'],
                'force' => [true],
            ],
            ['SIF_ENV' => 'production'],
            CliInteractionMode::nonInteractive(),
            new CliVerbosity('verbose'),
        );

        self::assertSame(['secret-value'], $invocation->option('token'));
        self::assertSame([
            'command' => 'installer:run',
            'argument_count' => 1,
            'option_names' => ['force', 'token'],
            'environment_count' => 1,
            'interaction' => 'non-interactive',
            'verbosity' => 'verbose',
        ], $invocation->safeSummary());
    }

    public function testGovernedExitCodesAreStable(): void
    {
        self::assertSame(0, CliExitCode::success()->value());
        self::assertSame('not-authorized', CliExitCode::notAuthorized()->label());
        self::assertFalse(CliExitCode::partialOrCompensated()->successful());

        $this->expectException(InvalidCliExitCodeException::class);
        new CliExitCode(99);
    }

    public function testCommandResultSummaryDoesNotExposePayloadValues(): void
    {
        $result = new CliCommandResult(
            CliExitCode::validationFailure(),
            'Configuration validation failed.',
            ['secret' => 'redacted-by-renderer', 'errors' => ['missing-key']],
            ['One optional extension is unavailable.'],
        );

        self::assertSame([
            'exit_code' => 4,
            'status' => 'validation-failure',
            'has_message' => true,
            'data_keys' => ['secret', 'errors'],
            'warning_count' => 1,
        ], $result->safeSummary());
    }
}
