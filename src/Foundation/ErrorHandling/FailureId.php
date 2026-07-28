<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureIdException;

final readonly class FailureId
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/D', $value) !== 1) {
            throw new InvalidFailureIdException('A failure identifier must be a portable non-empty identifier.');
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
