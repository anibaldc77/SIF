<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Audit;

use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Contracts\AuditIdGeneratorInterface;
use UnderflowException;

final class SequenceAuditIdGenerator implements AuditIdGeneratorInterface
{
    /**
     * @var list<string>
     */
    private array $values;

    /**
     * @param list<string> $values
     */
    public function __construct(array $values)
    {
        $this->values = array_values($values);
    }

    public function generate(): AuditId
    {
        $value = array_shift($this->values);

        if ($value === null) {
            throw new UnderflowException('No audit identifiers remain.');
        }

        return new AuditId($value);
    }
}
