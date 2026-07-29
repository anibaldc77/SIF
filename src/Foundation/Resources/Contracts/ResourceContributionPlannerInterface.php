<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Contribution\CompiledResourceContributionPlan;
use Sif\Foundation\Resources\Contribution\ModuleResourceContribution;

interface ResourceContributionPlannerInterface
{
    /** @param list<ModuleResourceContribution> $contributions */
    public function compile(array $contributions): CompiledResourceContributionPlan;
}
