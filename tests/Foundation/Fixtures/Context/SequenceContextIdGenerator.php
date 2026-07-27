<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Context;

use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Contracts\ContextIdGeneratorInterface;

/** Deterministic identifier generator for tests. */
final class SequenceContextIdGenerator implements ContextIdGeneratorInterface
{
    private int $position = 0;

    /** @param non-empty-list<non-empty-string> $values */
    public function __construct(private readonly array $values)
    {
    }

    public function generate(): ContextId
    {
        $value = $this->values[$this->position] ?? null;

        if ($value === null) {
            throw new \LogicException('No deterministic context identifier remains in the sequence.');
        }

        ++$this->position;

        return new ContextId($value);
    }
}
