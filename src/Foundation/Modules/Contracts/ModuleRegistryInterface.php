<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;

interface ModuleRegistryInterface
{
    public function has(ModuleId $id): bool;

    public function descriptor(ModuleId $id): ?ModuleDescriptor;

    public function module(ModuleId $id): ?ModuleInterface;

    /** @return list<ModuleDescriptor> */
    public function descriptors(): array;

    public function isFrozen(): bool;
}
