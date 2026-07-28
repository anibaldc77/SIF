<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Planning;

use Sif\Foundation\Modules\ModuleDescriptor;

final readonly class ResolvedModulePlan
{
    /**
     * @param list<ModuleDescriptor> $enabledModules
     * @param list<DisabledModule> $disabledModules
     * @param array<string, list<string>> $dependenciesByModule
     */
    public function __construct(
        private array $enabledModules,
        private array $disabledModules,
        private array $dependenciesByModule,
    ) {
    }

    /** @return list<ModuleDescriptor> */
    public function enabledModules(): array
    {
        return $this->enabledModules;
    }

    /** @return list<DisabledModule> */
    public function disabledModules(): array
    {
        return $this->disabledModules;
    }

    /** @return array<string, list<string>> */
    public function dependenciesByModule(): array
    {
        return $this->dependenciesByModule;
    }

    /** @return list<ModuleDescriptor> */
    public function shutdownOrder(): array
    {
        return array_reverse($this->enabledModules);
    }
}
