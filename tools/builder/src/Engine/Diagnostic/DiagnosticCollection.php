<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Diagnostic;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/** @implements IteratorAggregate<int, Diagnostic> */
final readonly class DiagnosticCollection implements Countable, IteratorAggregate
{
    /** @var list<Diagnostic> */
    private array $diagnostics;

    /** @param list<Diagnostic> $diagnostics */
    public function __construct(array $diagnostics = [])
    {
        foreach ($diagnostics as $diagnostic) {
            if (!$diagnostic instanceof Diagnostic) {
                throw new InvalidArgumentException('Diagnostics must contain only Diagnostic instances.');
            }
        }

        usort(
            $diagnostics,
            static fn (Diagnostic $left, Diagnostic $right): int => $left->identity() <=> $right->identity(),
        );

        $this->diagnostics = array_values($diagnostics);
    }

    public function with(Diagnostic $diagnostic): self
    {
        return new self([...$this->diagnostics, $diagnostic]);
    }

    public function merge(self $other): self
    {
        return new self([...$this->diagnostics, ...$other->diagnostics]);
    }

    /** @return list<Diagnostic> */
    public function all(): array
    {
        return $this->diagnostics;
    }

    public function count(): int
    {
        return count($this->diagnostics);
    }

    public function isEmpty(): bool
    {
        return $this->diagnostics === [];
    }

    public function hasSeverity(DiagnosticSeverity $severity): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if ($diagnostic->severity === $severity) {
                return true;
            }
        }

        return false;
    }

    public function hasErrors(): bool
    {
        return $this->hasSeverity(DiagnosticSeverity::ERROR)
            || $this->hasSeverity(DiagnosticSeverity::FATAL);
    }

    public function getIterator(): Traversable
    {
        yield from $this->diagnostics;
    }
}
