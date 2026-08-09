<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\CompensatingControl;
use Sif\Foundation\Security\Governance\CompensatingControlId;

interface CompensatingControlRepositoryInterface
{
    public function find(
        CompensatingControlId $id
    ): ?CompensatingControl;
}
