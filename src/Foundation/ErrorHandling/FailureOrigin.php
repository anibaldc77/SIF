<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureOriginException;

final readonly class FailureOrigin
{
    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if ($value === '' || preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/D', $value) !== 1) {
            throw new InvalidFailureOriginException('A failure origin must be a portable lowercase identifier.');
        }
        $this->value = $value;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
