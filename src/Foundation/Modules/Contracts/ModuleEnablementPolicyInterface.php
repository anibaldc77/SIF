<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\Enablement\ModuleEnablementDecision;
use Sif\Foundation\Modules\ModuleDescriptor;

interface ModuleEnablementPolicyInterface
{
    public function decide(ModuleDescriptor $descriptor): ModuleEnablementDecision;
}
