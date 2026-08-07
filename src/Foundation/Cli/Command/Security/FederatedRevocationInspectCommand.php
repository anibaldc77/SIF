<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use Sif\Foundation\Security\Contracts\FederatedRevocationJournalInterface;
use Sif\Foundation\Security\Operations\Revocation\FederatedRevocationOperationId;

final readonly class FederatedRevocationInspectCommand
{
    public function __construct(
        private FederatedRevocationJournalInterface $journal
    ) {
    }

    /** @return array<string, mixed> */
    public function execute(FederatedRevocationOperationId $operationId): array
    {
        $record = $this->journal->find($operationId);

        if ($record === null) {
            return [
                'found' => false,
                'operation_id' => $operationId->value(),
            ];
        }

        $execution = $record->execution();

        return [
            'found' => true,
            'operation_id' => $operationId->value(),
            'completed' => $record->completed(),
            'recorded_at' => $record->recordedAt()->format(DATE_ATOM),
            'scope' => $execution->request()->scope()->value,
            'reason' => $execution->request()->reason()->code(),
            'steps' => array_map(
                static fn ($step): array => [
                    'step' => $step->step()->value,
                    'attempted' => $step->attempted(),
                    'succeeded' => $step->succeeded(),
                    'failure_type' => $step->failureType(),
                ],
                $execution->steps()
            ),
        ];
    }
}
