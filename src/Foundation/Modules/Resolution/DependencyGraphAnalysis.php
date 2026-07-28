<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Resolution;

use Sif\Foundation\Modules\ModuleDescriptor;

final readonly class DependencyGraphAnalysis
{
    /**
     * @param list<ModuleDescriptor> $orderedDescriptors
     * @param array<string, list<string>> $dependenciesByModule
     */
    public function __construct(
        private array $orderedDescriptors,
        private array $dependenciesByModule,
    ) {
    }

    /** @return list<ModuleDescriptor> */
    public function orderedDescriptors(): array
    {
        return $this->orderedDescriptors;
    }

    /** @return array<string, list<string>> */
    public function dependenciesByModule(): array
    {
        return $this->dependenciesByModule;
    }
}
