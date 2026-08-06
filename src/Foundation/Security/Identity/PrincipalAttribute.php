<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Sif\Foundation\Security\Exceptions\InvalidPrincipalAttributeException;

final readonly class PrincipalAttribute
{
    public function __construct(private string $name, private string|int|float|bool|null $value)
    {
        if (preg_match('/^[a-z][a-z0-9_.:-]{0,127}$/D', $name) !== 1) {
            throw new InvalidPrincipalAttributeException(
                'Principal attribute name must use a stable lowercase identifier of at most 128 characters.'
            );
        }

        if (is_float($value) && !is_finite($value)) {
            throw new InvalidPrincipalAttributeException('Principal attribute floating-point values must be finite.');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): string|int|float|bool|null
    {
        return $this->value;
    }
}
