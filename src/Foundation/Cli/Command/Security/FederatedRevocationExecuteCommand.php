<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use DateTimeImmutable;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationCoordinator;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOperationId;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationRequest;

final readonly class FederatedRevocationExecuteCommand
{
    public function __construct(
        private FederatedRevocationCoordinator $coordinator
    ) {
    }

    /** @return array<string, mixed> */
    public function execute(
        FederatedRevocationOperationId $operationId,
        FederatedRevocationRequest $request,
        DateTimeImmutable $now,
        bool $confirmed
    ): array {
        if (!$confirmed) {
            return [
                'executed' => false,
                'operation_id' => $operationId->value(),
                'reason' => 'confirmation_required',
            ];
        }

        $execution = $this->coordinator->execute(
            $operationId,
            $request,
            $now
        );

        return [
            'executed' => true,
            'operation_id' => $operationId->value(),
            'completed' => $execution->succeeded(),
            'scope' => $request->scope()->value,
            'reason' => $request->reason()->code(),
        ];
    }
}
