<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Contracts\MutableModuleRegistryInterface;
use Sif\Foundation\Modules\Exceptions\DuplicateModuleException;
use Sif\Foundation\Modules\Exceptions\FrozenModuleRegistryException;

final class ModuleRegistry implements MutableModuleRegistryInterface
{
    /** @var array<string, ModuleDescriptor> */
    private array $descriptors = [];

    /** @var array<string, ModuleInterface> */
    private array $modules = [];

    private bool $frozen = false;

    public function register(ModuleInterface $module): void
    {
        $this->assertMutable();
        $descriptor = $module->descriptor();
        $key = $descriptor->id()->value();

        if (isset($this->descriptors[$key])) {
            throw DuplicateModuleException::forId($descriptor->id());
        }

        $this->descriptors[$key] = $descriptor;
        $this->modules[$key] = $module;
    }

    public function has(ModuleId $id): bool
    {
        return isset($this->descriptors[$id->value()]);
    }

    public function descriptor(ModuleId $id): ?ModuleDescriptor
    {
        return $this->descriptors[$id->value()] ?? null;
    }

    public function module(ModuleId $id): ?ModuleInterface
    {
        return $this->modules[$id->value()] ?? null;
    }

    /** @return list<ModuleDescriptor> */
    public function descriptors(): array
    {
        return array_values($this->descriptors);
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    private function assertMutable(): void
    {
        if ($this->frozen) {
            throw FrozenModuleRegistryException::mutationAttempted();
        }
    }
}
