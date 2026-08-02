<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\BootStage;
use Sif\Foundation\Cli\Command\Configuration\ConfigurationValidateCommand;
use Sif\Foundation\Cli\Command\Runtime\RuntimeAboutCommand;
use Sif\Foundation\Cli\Command\Runtime\RuntimeCapabilitiesCommand;
use Sif\Foundation\Cli\Command\Runtime\RuntimeDoctorCommand;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ConfigurationRepository;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Schema\ConfigurationSchema;
use Sif\Foundation\Configuration\Schema\ConfigurationSchemaRule;
use Sif\Foundation\Runtime;
use Sif\Foundation\RuntimeState;

final class CliRuntimeConfigurationCommandTest extends TestCase
{
    public function testRuntimeInspectionCommandsExposeSafeDeterministicData(): void
    {
        $runtime = new Runtime();
        $runtime->transitionTo(RuntimeState::Bootstrapping, BootStage::Bootstrap);

        $about = (new RuntimeAboutCommand($runtime, ['models', 'runtime', 'models']))
            ->execute(new CliInvocation(new CliCommandName('runtime:about')));
        $capabilities = (new RuntimeCapabilitiesCommand(['models', 'runtime', 'models']))
            ->execute(new CliInvocation(new CliCommandName('runtime:capabilities')));

        self::assertSame(0, $about->exitCode()->value());
        self::assertSame('bootstrapping', $about->data()['state']);
        self::assertSame(2, $about->data()['capability_count']);
        self::assertSame(['models', 'runtime'], $capabilities->data()['capabilities']);
    }

    public function testDoctorReportsHealthyRuntimeWithoutMutatingConfiguration(): void
    {
        $configuration = new ConfigurationRepository(['app' => ['name' => 'SIF']]);
        $result = (new RuntimeDoctorCommand(new Runtime(), $configuration))
            ->execute(new CliInvocation(new CliCommandName('runtime:doctor')));

        self::assertSame(0, $result->exitCode()->value());
        self::assertTrue($result->data()['healthy']);
        self::assertFalse($configuration->isFrozen());
    }

    public function testConfigurationValidationReturnsGovernedFailure(): void
    {
        $configuration = new ImmutableConfigurationRepository(['app' => ['debug' => 'yes']]);
        $schema = new ConfigurationSchema([
            new ConfigurationSchemaRule(
                new ConfigurationKey('app.debug'),
                ConfigurationValueType::Boolean,
                required: true,
            ),
        ]);

        $result = (new ConfigurationValidateCommand($configuration, $schema))
            ->execute(new CliInvocation(new CliCommandName('config:validate')));

        self::assertSame(4, $result->exitCode()->value());
        self::assertFalse($result->data()['valid']);
        self::assertSame(1, $result->data()['issue_count']);
        self::assertSame('app.debug', $result->data()['issues'][0]['key']);
    }
}
