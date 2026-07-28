<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

interface MutableModuleRegistryInterface extends ModuleRegistryInterface
{
    public function register(ModuleInterface $module): void;

    public function freeze(): void;
}
