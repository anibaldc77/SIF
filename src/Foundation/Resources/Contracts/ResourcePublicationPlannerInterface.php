<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Publication\CompiledResourcePublicationPlan;
use Sif\Foundation\Resources\Publication\ResourcePublicationRequest;

interface ResourcePublicationPlannerInterface
{
    /** @param list<ResourcePublicationRequest> $requests */
    public function compile(array $requests): CompiledResourcePublicationPlan;
}
