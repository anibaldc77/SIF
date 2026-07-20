<?php

declare(strict_types=1);

namespace Sif\Builder\Reference;

use Sif\Builder\Reference\Exception\DuplicateReferenceException;

final class ReferenceCollection
{
    /** @var array<string, Reference> */
    private array $references = [];

    public function add(Reference $reference): void
    {
        $identity = $reference->identity();

        if (isset($this->references[$identity])) {
            throw DuplicateReferenceException::forIdentity($identity);
        }

        $this->references[$identity] = $reference;
    }

    public function remove(Reference $reference): bool
    {
        $identity = $reference->identity();

        if (!isset($this->references[$identity])) {
            return false;
        }

        unset($this->references[$identity]);

        return true;
    }

    public function contains(Reference $reference): bool
    {
        return isset($this->references[$reference->identity()]);
    }

    /** @return list<Reference> */
    public function all(): array
    {
        $references = $this->references;
        ksort($references, SORT_STRING);

        return array_values($references);
    }

    public function count(): int
    {
        return count($this->references);
    }

    public function isEmpty(): bool
    {
        return $this->references === [];
    }

    /** @return list<Reference> */
    public function bySource(string $sourceIdentifier): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Reference $reference): bool => $reference->sourceIdentifier === $sourceIdentifier,
        ));
    }

    /** @return list<Reference> */
    public function byTarget(string $targetIdentifier): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Reference $reference): bool => $reference->targetIdentifier === $targetIdentifier,
        ));
    }
}
