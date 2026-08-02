<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Module;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Modules\Contracts\ModuleRegistryInterface;
use Sif\Foundation\Modules\ModuleDescriptor;

final readonly class ModuleListCommand implements CliCommandInterface
{
    public function __construct(private ModuleRegistryInterface $registry)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('module:list'),
            'Lists registered modules.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $descriptors = $this->registry->descriptors();
        usort(
            $descriptors,
            static fn (ModuleDescriptor $left, ModuleDescriptor $right): int =>
                $left->id()->value() <=> $right->id()->value(),
        );

        $modules = array_map(
            static fn (ModuleDescriptor $descriptor): array => [
                'id' => $descriptor->id()->value(),
                'name' => $descriptor->name(),
                'version' => $descriptor->version()->value(),
                'required_dependency_count' => count($descriptor->requiredDependencies()),
                'optional_dependency_count' => count($descriptor->optionalDependencies()),
                'provided_capabilities' => $descriptor->providedCapabilities(),
            ],
            $descriptors,
        );

        return new CliCommandResult(
            CliExitCode::success(),
            'Registered modules',
            [
                'modules' => $modules,
                'count' => count($modules),
                'frozen' => $this->registry->isFrozen(),
            ],
        );
    }
}
