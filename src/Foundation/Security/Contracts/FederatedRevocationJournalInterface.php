<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationExecutionRecord;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOperationId;

interface FederatedRevocationJournalInterface
{
    public function find(
        FederatedRevocationOperationId $operationId
    ): ?FederatedRevocationExecutionRecord;

    public function save(
        FederatedRevocationExecutionRecord $record
    ): void;
}
