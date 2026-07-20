<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Resolution;

use InvalidArgumentException;

final readonly class ResolutionResult
{
    /** @var list<ResolvedReference> */
    public array $resolved;

    /** @var list<BrokenReference> */
    public array $broken;

    /**
     * @param list<ResolvedReference> $resolved
     * @param list<BrokenReference> $broken
     */
    public function __construct(array $resolved = [], array $broken = [])
    {
        foreach ($resolved as $item) {
            if (!$item instanceof ResolvedReference) {
                throw new InvalidArgumentException('Resolved references must contain only ResolvedReference instances.');
            }
        }

        foreach ($broken as $item) {
            if (!$item instanceof BrokenReference) {
                throw new InvalidArgumentException('Broken references must contain only BrokenReference instances.');
            }
        }

        usort(
            $resolved,
            static fn (ResolvedReference $left, ResolvedReference $right): int =>
                $left->reference->identity() <=> $right->reference->identity(),
        );
        usort(
            $broken,
            static fn (BrokenReference $left, BrokenReference $right): int =>
                $left->reference->identity() <=> $right->reference->identity(),
        );

        $this->resolved = array_values($resolved);
        $this->broken = array_values($broken);
    }

    public function total(): int
    {
        return count($this->resolved) + count($this->broken);
    }

    public function resolvedCount(): int
    {
        return count($this->resolved);
    }

    public function brokenCount(): int
    {
        return count($this->broken);
    }

    public function isSuccessful(): bool
    {
        return $this->broken === [];
    }

    public function isEmpty(): bool
    {
        return $this->total() === 0;
    }
}
