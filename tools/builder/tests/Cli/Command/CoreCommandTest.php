<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Cli\Command;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Cli\Command\HelpCommand;
use Sif\Builder\Cli\Command\ListCommand;
use Sif\Builder\Cli\Command\VersionCommand;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;
use Sif\Builder\Cli\Registry\CommandRegistry;
use Sif\Builder\Cli\Runtime\StaticComponentCatalog;
use Sif\Builder\Cli\Runtime\StaticVersionProvider;

final class CoreCommandTest extends TestCase
{
    public function testVersionCommandUsesTheInjectedProvider(): void
    {
        $command = new VersionCommand(new StaticVersionProvider('SIF Builder', '2.0.0-alpha1'));

        $result = $command->execute(new CommandInput('version'));

        self::assertSame(ExitCode::SUCCESS, $result->exitCode);
        self::assertSame("SIF Builder 2.0.0-alpha1\n", $result->standardOutput);
    }

    public function testListCommandPreservesCatalogOrder(): void
    {
        $command = new ListCommand(new StaticComponentCatalog(
            ['reference.broken', 'repository.metadata'],
            ['repository.index'],
            ['report.markdown', 'report.json'],
        ));

        $output = (string) $command->execute(new CommandInput('list'))->standardOutput;

        self::assertLessThan(strpos($output, 'repository.metadata'), strpos($output, 'reference.broken'));
        self::assertStringContainsString('report.markdown', $output);
        self::assertStringContainsString('report.json', $output);
    }

    public function testHelpCommandUsesRegistryInsertionOrderAndSupportsACommandTarget(): void
    {
        $version = new StaticVersionProvider('SIF Builder', '2.0.0-alpha1');
        $registry = new CommandRegistry();
        $help = new HelpCommand($registry, $version);
        $registry->register($help);
        $registry->register(new VersionCommand($version));
        $registry->register(new ListCommand(new StaticComponentCatalog()));

        $global = (string) $help->execute(new CommandInput('help'))->standardOutput;
        $target = (string) $help->execute(new CommandInput('help', ['version']))->standardOutput;

        self::assertLessThan(strpos($global, 'version'), strpos($global, 'help'));
        self::assertLessThan(strpos($global, 'list'), strpos($global, 'version'));
        self::assertStringContainsString('Command: version', $target);
        self::assertStringContainsString('Display the SIF Builder version.', $target);
    }

    public function testCoreCommandsRejectUnsupportedInput(): void
    {
        $version = new StaticVersionProvider('SIF Builder', '2.0.0-alpha1');
        $list = new ListCommand(new StaticComponentCatalog());
        $versionCommand = new VersionCommand($version);

        self::assertSame(
            ExitCode::INVALID_USAGE,
            $versionCommand->execute(new CommandInput('version', ['unexpected']))->exitCode,
        );
        self::assertSame(
            ExitCode::INVALID_USAGE,
            $list->execute(new CommandInput('list', [], [], ['verbose']))->exitCode,
        );
    }
}
