<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Cli;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Maintenance\MaintenanceSummaryCommand;
use Sif\Foundation\Cli\Command\Module\ModuleListCommand;
use Sif\Foundation\Cli\Command\Resource\ResourceInspectCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Extension\CliCommandContributorCollection;
use Sif\Foundation\Cli\Extension\CliCommandContributorInterface;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;
use Sif\Foundation\Resources\Registry\ResourceRegistry;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;
use Sif\Foundation\Resources\ResourcePath;
use Sif\Foundation\Resources\ResourceType;

final class CliModuleResourceExtensibilityCommandTest extends TestCase
{
    public function testModuleListIsDeterministic(): void
    {
        $registry = new ModuleRegistry();
        $registry->register(new TestModule(new ModuleDescriptor(new ModuleId('zeta'), new ModuleVersion('1.0.0'), 'Zeta')));
        $registry->register(new TestModule(new ModuleDescriptor(new ModuleId('alpha'), new ModuleVersion('2.0.0'), 'Alpha')));

        $result = (new ModuleListCommand($registry))->execute(new CliInvocation(new CliCommandName('module:list')));

        self::assertSame(0, $result->exitCode()->value());
        self::assertSame(['alpha', 'zeta'], array_column($result->data()['modules'], 'id'));
    }

    public function testResourceInspectReturnsSafeDescriptorSummary(): void
    {
        $registry = new ResourceRegistry();
        $registry->register(new ResourceDescriptor(
            new ResourceIdentifier('logo'),
            new ResourceNamespace('app'),
            ResourceType::image(),
            new ResourcePath('assets/logo.png'),
        ));

        $result = (new ResourceInspectCommand($registry))->execute(
            new CliInvocation(new CliCommandName('resource:inspect'), ['app', 'logo']),
        );

        self::assertSame(0, $result->exitCode()->value());
        self::assertSame('app:logo', $result->data()['resource']['namespace'] . ':' . $result->data()['resource']['identifier']);
    }

    public function testContributorsRegisterCommandsDeterministically(): void
    {
        $collection = new CliCommandContributorCollection([
            new TestContributor([
                new MaintenanceSummaryCommand(['status' => 'ready']),
            ]),
        ]);
        $registry = new CliCommandRegistry();

        $collection->registerInto($registry);

        self::assertSame(1, $collection->count());
        self::assertTrue($registry->has('maintenance:summary'));
        self::assertCount(1, $collection->commands());
    }
}

final readonly class TestModule implements ModuleInterface
{
    public function __construct(private ModuleDescriptor $descriptor)
    {
    }

    public function descriptor(): ModuleDescriptor
    {
        return $this->descriptor;
    }
}

final readonly class TestContributor implements CliCommandContributorInterface
{
    /** @param list<CliCommandInterface> $commands */
    public function __construct(private array $commands)
    {
    }

    public function commands(): array
    {
        return $this->commands;
    }
}
