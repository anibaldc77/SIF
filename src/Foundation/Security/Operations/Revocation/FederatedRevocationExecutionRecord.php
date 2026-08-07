<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateTimeImmutable;

final readonly class FederatedRevocationExecutionRecord
{
    public function __construct(
        private FederatedRevocationOperationId $operationId,
        private FederatedRevocationExecution $execution,
        private DateTimeImmutable $recordedAt
    ) {
    }

    public function operationId(): FederatedRevocationOperationId
    {
        return $this->operationId;
    }

    public function execution(): FederatedRevocationExecution
    {
        return $this->execution;
    }

    public function recordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function completed(): bool
    {
        return $this->execution->succeeded();
    }
}
