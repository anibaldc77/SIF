<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Generation;

final readonly class SkeletonGenerationPlan
{
    /** @var list<SkeletonGenerationEntry> */
    private array $entries;
    private string $fingerprint;

    /** @param iterable<SkeletonGenerationEntry> $entries */
    public function __construct(iterable $entries)
    {
        $normalized = [];
        foreach ($entries as $entry) {
            $normalized[] = $entry;
        }
        $this->entries = $normalized;

        $json = json_encode($this->summary(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $this->fingerprint = hash('sha256', $json);
    }

    /** @return list<SkeletonGenerationEntry> */
    public function entries(): array
    {
        return $this->entries;
    }

    public function executable(): bool
    {
        foreach ($this->entries as $entry) {
            if ($entry->action() === SkeletonGenerationAction::Conflict) {
                return false;
            }
        }

        return true;
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }

    /** @return list<array<string, string|null>> */
    public function summary(): array
    {
        return array_map(
            static fn (SkeletonGenerationEntry $entry): array => $entry->summary(),
            $this->entries,
        );
    }
}
