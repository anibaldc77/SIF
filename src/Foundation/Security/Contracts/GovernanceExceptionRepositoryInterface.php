<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\GovernanceException;
use Sif\Foundation\Security\Governance\GovernanceExceptionId;

interface GovernanceExceptionRepositoryInterface
{
    public function find(
        GovernanceExceptionId $id
    ): ?GovernanceException;

    public function save(
        GovernanceException $exception
    ): void;
}
