<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Exceptions\InvalidMutationJournalEntryException;

final readonly class MutationJournalEntry
{
    private int $sequence;
    private MutationExecutionResult $result;

    public function __construct(int $sequence, MutationExecutionResult $result)
    {
        if ($sequence < 1) {
            throw new InvalidMutationJournalEntryException('Mutation journal sequence numbers must be positive.');
        }

        $this->sequence = $sequence;
        $this->result = $result;
    }

    public function sequence(): int { return $this->sequence; }
    public function result(): MutationExecutionResult { return $this->result; }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        return [
            'sequence' => $this->sequence,
            'result' => $this->result->summary(),
        ];
    }
}
