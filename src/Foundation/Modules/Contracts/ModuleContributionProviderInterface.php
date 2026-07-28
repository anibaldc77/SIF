<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Contracts;

use Sif\Foundation\Modules\Contribution\ModuleContributionSet;

interface ModuleContributionProviderInterface
{
    public function contributions(): ModuleContributionSet;
}
