<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\ModuleDescriptor;

interface ModuleDescriptorProviderInterface
{
    public function descriptor(): ModuleDescriptor;
}
