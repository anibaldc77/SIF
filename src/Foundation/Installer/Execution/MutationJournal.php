<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Execution;

use Sif\Foundation\Installer\Exceptions\InvalidMutationJournalException;

final readonly class MutationJournal
{
    /** @var list<MutationJournalEntry> */
    private array $entries;

    /** @param iterable<MutationJournalEntry> $entries */
    public function __construct(iterable $entries)
    {
        $normalized = [];
        $expectedSequence = 1;
        foreach ($entries as $entry) {
            if (!$entry instanceof MutationJournalEntry) {
                throw new InvalidMutationJournalException('Mutation journals accept only MutationJournalEntry instances.');
            }
            if ($entry->sequence() !== $expectedSequence) {
                throw new InvalidMutationJournalException('Mutation journal entries must use contiguous sequence numbers starting at one.');
            }
            $normalized[] = $entry;
            ++$expectedSequence;
        }

        $this->entries = $normalized;
    }

    /** @return list<MutationJournalEntry> */
    public function entries(): array { return $this->entries; }

    /** @return list<array<string, mixed>> */
    public function summary(): array
    {
        return array_map(
            static fn (MutationJournalEntry $entry): array => $entry->summary(),
            $this->entries,
        );
    }
}
