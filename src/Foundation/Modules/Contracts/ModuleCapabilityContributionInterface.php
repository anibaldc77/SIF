<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;

interface ModuleCapabilityContributionInterface
{
    /** @return list<CapabilityInterface> */
    public function capabilities(): array;
}
